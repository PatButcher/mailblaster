<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpProvider;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\EmailLog;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $totalSmtp = SmtpProvider::count();
        $activeSmtp = SmtpProvider::where('active', true)->count();
        $totalContacts = Contact::count();
        $activeContacts = Contact::where('subscribed', true)->count();
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::whereIn('status', ['sending', 'queued'])->count();
        $totalEmailsSent = EmailLog::where('status', 'sent')->count();
        $totalEmailsFailed = EmailLog::where('status', 'failed')->count();
        $totalEmailsQueued = EmailLog::where('status', 'queued')->count();
        $deliveryRate = ($totalEmailsSent + $totalEmailsFailed) > 0
            ? round(($totalEmailsSent / ($totalEmailsSent + $totalEmailsFailed)) * 100, 1)
            : 0;

        $recentCampaigns = Campaign::orderBy('created_at', 'desc')->take(5)->get();
        $recentLogs = EmailLog::with(['campaign', 'smtpProvider'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $smtpUsage = SmtpProvider::where('active', true)
            ->orderBy('daily_sent_count', 'desc')
            ->get();

        $dailyStats = EmailLog::selectRaw('DATE(created_at) as date, COUNT(*) as total,
            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'totalSmtp', 'activeSmtp', 'totalContacts', 'activeContacts',
            'totalCampaigns', 'activeCampaigns', 'totalEmailsSent', 'totalEmailsFailed',
            'totalEmailsQueued', 'deliveryRate', 'recentCampaigns', 'recentLogs',
            'smtpUsage', 'dailyStats'
        ));
    }
}