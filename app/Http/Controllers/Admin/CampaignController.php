<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Models\SmtpProvider;
use App\Models\AdminNotification;
use App\Models\MailingList; // Import MailingList model
use App\Services\EmailDispatchService;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $query = Campaign::withCount(['emailLogs', 'emailLogs as sent_count' => function ($q) {
            $q->where('status', 'sent');
        }, 'emailLogs as failed_count' => function ($q) {
            $q->where('status', 'failed');
        }])->orderBy('created_at', 'desc');
        if ($request->filled('status')) $query->where('status', $request->status);
        $campaigns = $query->paginate(15)->withQueryString();
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        if ($r = $this->authCheck()) return $r;
        $contactCount = Contact::where('subscribed', true)->count();
        $smtpProviders = SmtpProvider::where('active', true)->orderBy('priority')->get();
        $mailingLists = MailingList::orderBy('name')->get(); // Retrieve all mailing lists
        return view('admin.campaigns.create', compact('contactCount', 'smtpProviders', 'mailingLists'));
    }

    public function store(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'subject'               => 'required|string|max:500',
            'from_name'             => 'required|string|max:255',
            'from_email'            => 'required|email|max:255',
            'reply_to'              => 'nullable|email|max:255',
            'body_html'             => 'required|string',
            'body_text'             => 'nullable|string',
            'recipient_filter'      => 'required|in:all,subscribed,tagged',
            'tags_filter'           => 'nullable|string|max:500',
            'scheduled_at'          => 'nullable|date|after:now',
            'batch_size'            => 'required|integer|min:1|max:500000',
            'delay_between_batches' => 'required|integer|min:0|max:88000',
            'is_recurring'          => 'nullable|boolean',
        ]);
        $validated['status'] = 'draft';
        $validated['created_by'] = session('admin_user');
        $validated['is_recurring'] = $request->has('is_recurring');
        Campaign::create($validated);
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign created successfully.');
    }

    public function show($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::withCount(['emailLogs', 'emailLogs as sent_count' => function ($q) {
            $q->where('status', 'sent');
        }, 'emailLogs as failed_count' => function ($q) {
            $q->where('status', 'failed');
        }, 'emailLogs as queued_count' => function ($q) {
            $q->where('status', 'queued');
        }])->findOrFail($id);
        $logs = EmailLog::where('campaign_id', $id)->with('smtpProvider')->orderBy('created_at', 'desc')->paginate(20);
        $smtpStats = EmailLog::where('campaign_id', $id)->with('smtpProvider')
            ->selectRaw('smtp_provider_id, status, COUNT(*) as count')
            ->groupBy('smtp_provider_id', 'status')->get();
        return view('admin.campaigns.show', compact('campaign', 'logs', 'smtpStats'));
    }

    public function edit($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        if (!in_array($campaign->status, ['draft', 'paused'])) {
            return redirect()->route('admin.campaigns.show', $id)->with('error', 'Only draft or paused campaigns can be edited.');
        }
        $smtpProviders = SmtpProvider::where('active', true)->orderBy('priority')->get();
        return view('admin.campaigns.edit', compact('campaign', 'smtpProviders'));
    }

    public function update(Request $request, $id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'subject'               => 'required|string|max:500',
            'from_name'             => 'required|string|max:255',
            'from_email'            => 'required|email|max:255',
            'reply_to'              => 'nullable|email|max:255',
            'body_html'             => 'required|string',
            'body_text'             => 'nullable|string',
            'recipient_filter'      => 'required|in:all,subscribed,tagged',
            'tags_filter'           => 'nullable|string|max:500',
            'scheduled_at'          => 'nullable|date',
            'batch_size'            => 'required|integer|min:1|max:500000',
            'delay_between_batches' => 'required|integer|min:0|max:88000',
            'is_recurring'          => 'nullable|boolean',
        ]);
        $validated['is_recurring'] = $request->has('is_recurring');
        $campaign->update($validated);
        return redirect()->route('admin.campaigns.show', $id)->with('success', 'Campaign updated.');
    }

    public function destroy($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        if ($campaign->status === 'sending') return back()->with('error', 'Cannot delete a sending campaign.');
        EmailLog::where('campaign_id', $id)->delete();
        $campaign->delete();
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign deleted.');
    }

    public function send(Request $request, $id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        if (!in_array($campaign->status, ['draft', 'paused'])) {
            return back()->with('error', 'Campaign cannot be sent in its current status.');
        }
        if (!SmtpProvider::where('active', true)->count()) {
            return back()->with('error', 'No active SMTP providers available.');
        }

        // SYNC
        $service = new EmailDispatchService();
        $result  = $service->queueCampaign($campaign);
        if ($result['success']) {
            $campaign->update(['status' => 'queued', 'started_at' => now()]);
            return back()->with('success', "Campaign queued: {$result['queued']} emails added to the queue.");
        }

        // // Dispatch the job to queue emails in the background
        // QueueCampaignEmailsJob::dispatch($campaign);

        return back()->with('error', 'Failed to queue: ' . $result['message']);
    }

    public function pause($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        if (!in_array($campaign->status, ['queued', 'sending'])) return back()->with('error', 'Cannot pause.');
        $campaign->update(['status' => 'paused']);
        EmailLog::where('campaign_id', $id)->where('status', 'queued')->update(['status' => 'paused']);
        return back()->with('success', 'Campaign paused.');
    }

    public function resume($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        if ($campaign->status !== 'paused') return back()->with('error', 'Only paused campaigns can be resumed.');
        $campaign->update(['status' => 'queued']);
        EmailLog::where('campaign_id', $id)->where('status', 'paused')->update(['status' => 'queued']);
        return back()->with('success', 'Campaign resumed.');
    }

    public function cancel($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        $campaign->update(['status' => 'cancelled', 'completed_at' => now()]);
        EmailLog::where('campaign_id', $id)->whereIn('status', ['queued', 'paused'])->update(['status' => 'cancelled']);
        return back()->with('success', 'Campaign cancelled.');
    }

    public function recycle($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);

        if (!$campaign->is_recurring) {
            return back()->with('error', 'This campaign is not set as recurring.');
        }

        if (!in_array($campaign->status, ['completed', 'cancelled', 'failed'])) {
            return back()->with('error', 'Only completed, cancelled, or failed campaigns can be recycled.');
        }

        // Reset campaign status and timestamps for re-queuing
        $campaign->update([
            'status'         => 'draft', // Or 'queued' if it should be immediately picked up
            'started_at'     => null,
            'completed_at'   => null,
            'total_recipients' => 0, // Reset recipient count
        ]);

        // Optionally, delete or update existing email logs for a fresh start
        // For now, let's keep them but they won't be re-processed as status is draft
        // EmailLog::where('campaign_id', $id)->delete();

        AdminNotification::recordNotification(
            'campaign_recycled',
            'Campaign Recycled',
            "" . $campaign->name . " has been recycled and is ready for re-sending.",
            'refresh', 'green',
            route('admin.campaigns.show', $campaign->id)
        );

        return redirect()->route('admin.campaigns.show', $id)
            ->with('success', 'Campaign recycled successfully and ready to be re-sent.');
    }

    // ─── Duplicate ────────────────────────────────────────────
    public function duplicate($id)
    {
        if ($r = $this->authCheck()) return $r;
        $original = Campaign::findOrFail($id);
        $copy = $original->replicate();
        $copy->name         = 'Copy of ' . $original->name;
        $copy->status       = 'draft';
        $copy->started_at   = null;
        $copy->completed_at = null;
        $copy->scheduled_at = null;
        $copy->total_recipients = 0;
        $copy->duplicated_from  = $original->id;
        $copy->created_by   = session('admin_user');
        $copy->save();

        AdminNotification::recordNotification(
            'campaign_duplicated',
            'Campaign Duplicated',
            "" . $original->name . " was duplicated as " . $copy->name . ".",
            'copy', 'indigo',
            route('admin.campaigns.edit', $copy->id)
        );

        return redirect()->route('admin.campaigns.edit', $copy->id)
            ->with('success', 'Campaign duplicated. You can now edit and send the copy.');
    }

    // ─── Reschedule ───────────────────────────────────────────
    public function rescheduleForm($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        return view('admin.campaigns.reschedule', compact('campaign'));
    }

    public function reschedule(Request $request, $id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);
        // Reset the campaign so it can be sent again
        EmailLog::where('campaign_id', $id)->whereIn('status', ['cancelled', 'failed'])->delete();
        $campaign->update([
            'status'         => 'draft',
            'scheduled_at'   => $request->scheduled_at,
            'started_at'     => null,
            'completed_at'   => null,
            'total_recipients' => 0,
        ]);

        AdminNotification::recordNotification(
            'campaign_rescheduled',
            'Campaign Rescheduled',
            "" . $campaign->name . " rescheduled for " . $request->scheduled_at . ".",
            'calendar', 'blue',
            route('admin.campaigns.show', $campaign->id)
        );

        return redirect()->route('admin.campaigns.show', $id)
            ->with('success', 'Campaign rescheduled for ' . $request->scheduled_at . '. Click Send when ready.');
    }
}