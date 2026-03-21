<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Campaign;
use App\Services\EmailDispatchService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmailLogController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $query = EmailLog::with(['campaign', 'smtpProvider']);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('campaign_id')) $query->where('campaign_id', $request->campaign_id);
        if ($request->filled('search'))      $query->where('recipient_email', 'like', '%' . $request->search . '%');
        if ($request->filled('date_from'))   $query->whereDate('sent_at', '>=', $request->date_from);

        // TODO: HOOK UP TO FRONT
        // if ($request->filled('sent_at'))     $query->whereDate('sent_at', '>=', $request->sent_at);
        $query->where('sent_at', '!=', null);

        if ($request->filled('date_to'))     $query->whereDate('sent_at', '<=', $request->date_to);
        if ($request->filled('type')) {
            $query->where('is_single_send', $request->type === 'single');
        }

        $logs      = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();
        $campaigns = Campaign::orderBy('name')->get();
        $statsToday = EmailLog::whereDate('created_at', today())
            ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');


        // $sentAt = Carbon::parse($logs['sent_at'])->format('H:i:s - d m Y');
        $sentAt = Carbon::parse($logs['sent_at']);


        // $sentAt = Carbon::parse($logs->sent_at);

        return view('admin.logs.index', compact('logs', 'campaigns', 'statsToday', 'sentAt'));
    }

    public function show($id)
    {
        if ($r = $this->authCheck()) return $r;
        $log = EmailLog::with(['campaign', 'smtpProvider'])->findOrFail($id);
        return view('admin.logs.show', compact('log'));
    }

    public function retry($id)
    {
        if ($r = $this->authCheck()) return $r;
        $log = EmailLog::findOrFail($id);
        if ($log->status !== 'failed') return back()->with('error', 'Only failed emails can be retried.');
        $service = new EmailDispatchService();
        $result  = $service->retrySingleEmail($log);
        return back()->with($result['success'] ? 'success' : 'error', $result['success'] ? 'Email retried successfully.' : 'Retry failed: ' . $result['message']);
    }

    public function clearLogs(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $request->validate(['older_than_days' => 'required|integer|min:1|max:365']);
        $deleted = EmailLog::where('created_at', '<', now()->subDays($request->older_than_days))
            ->where('status', '!=', 'queued')->delete();
        return back()->with('success', "{$deleted} log entries cleared.");
    }

    // ─── CSV Export ───────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $query = EmailLog::with(['campaign', 'smtpProvider']);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('campaign_id')) $query->where('campaign_id', $request->campaign_id);
        if ($request->filled('date_from'))   $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))     $query->whereDate('created_at', '<=', $request->date_to);
        $logs     = $query->orderBy('created_at', 'desc')->get();
        $filename = 'email_logs_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];
        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Campaign', 'SMTP Provider', 'Recipient Email', 'Recipient Name',
                'Subject', 'Status', 'Attempts', 'Message ID', 'SMTP Response Code',
                'Error', 'Sent At', 'Failed At', 'Type', 'Created At'
            ]);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->campaign->name ?? 'Single Send',
                    $log->smtpProvider->name ?? 'N/A',
                    $log->recipient_email,
                    $log->recipient_name,
                    $log->subject,
                    $log->status,
                    $log->attempts,
                    $log->message_id,
                    $log->smtp_response_code,
                    $log->error_message,
                    $log->sent_at?->format('Y-m-d H:i:s'),
                    $log->failed_at?->format('Y-m-d H:i:s'),
                    $log->is_single_send ? 'Single Send' : 'Campaign',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportByCampaign($id)
    {
        if ($r = $this->authCheck()) return $r;
        $campaign = Campaign::findOrFail($id);
        $logs     = EmailLog::where('campaign_id', $id)->with('smtpProvider')->get();
        $filename = 'campaign_' . $id . '_logs_' . date('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];
        $callback = function () use ($logs, $campaign) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Campaign: ' . $campaign->name]);
            fputcsv($file, ['Exported: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Recipient Email', 'Recipient Name', 'SMTP Provider', 'Status', 'Attempts', 'SMTP Code', 'Error', 'Sent At']);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->recipient_email,
                    $log->recipient_name,
                    $log->smtpProvider->name ?? 'N/A',
                    $log->status,
                    $log->attempts,
                    $log->smtp_response_code,
                    $log->error_message,
                    $log->sent_at?->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}