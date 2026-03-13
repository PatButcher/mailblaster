<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\SmtpProvider;
use App\Services\EmailDispatchService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $perPage = $request->input('per_page', 30); // Default to 30 if not specified

        $queuedEmails = EmailLog::with(['campaign', 'smtpProvider'])
            ->where('status', 'queued')
            ->orderBy('created_at')
            ->paginate($perPage)
            ->withQueryString(); // Maintain query string for pagination links

        $queueStats = [
            'queued' => EmailLog::where('status', 'queued')->count(),
            'sending' => EmailLog::where('status', 'sending')->count(),
            'sent' => EmailLog::where('status', 'sent')->whereDate('created_at', today())->count(),
            'failed' => EmailLog::where('status', 'failed')->whereDate('created_at', today())->count(),
        ];

        $smtpStatus = SmtpProvider::where('active', true)
            ->orderBy('priority')
            ->get()
            ->map(function ($smtp) {
                $smtp->remaining_today = max(0, $smtp->max_daily_emails - $smtp->daily_sent_count);
                $smtp->usage_percent = $smtp->max_daily_emails > 0
                    ? round(($smtp->daily_sent_count / $smtp->max_daily_emails) * 100, 1)
                    : 0;
                return $smtp;
            });

        return view('admin.queue.index', compact('queuedEmails', 'queueStats', 'smtpStatus', 'perPage'));
    }

    public function processQueue(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $request->validate(['batch_limit' => 'nullable|integer|min:1|max:500000']);
        $limit = $request->batch_limit ?? 500000;

        $service = new EmailDispatchService();
        $result = $service->processQueue($limit);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            "Queue processed: {$result['sent']} sent, {$result['failed']} failed, {$result['skipped']} skipped (SMTP limit reached)."
        );
    }

    public function clearFailed()
    {
        if ($r = $this->authCheck()) return $r;
        $count = EmailLog::where('status', 'failed')->delete();
        return back()->with('success', "{$count} failed email logs cleared.");
    }

    public function deleteByRange(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $validated = $request->validate([
            'start_id' => 'required|integer|min:1',
            'end_id'   => 'required|integer|min:1|gte:start_id',
        ]);

        $deletedCount = EmailLog::whereBetween('id', [$validated['start_id'], $validated['end_id']])->delete();

        return back()->with('success', "{$deletedCount} email logs deleted in ID range {$validated['start_id']} to {$validated['end_id']}.");
    }
}