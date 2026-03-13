@extends('layouts.admin')
@section('title', 'Dashboard - MailBlast')
@section('page-title', 'Dashboard')

@section('content')
<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-paper-plane text-indigo-600 text-xl"></i>
            </div>
            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">Total Sent</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalEmailsSent) }}</p>
        <p class="text-sm text-gray-500 mt-1">Emails Delivered</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-stream text-yellow-600 text-xl"></i>
            </div>
            <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">Queued</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalEmailsQueued) }}</p>
        <p class="text-sm text-gray-500 mt-1">Awaiting Send</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            </div>
            <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full">Failed</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalEmailsFailed) }}</p>
        <p class="text-sm text-gray-500 mt-1">Delivery Failures</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
            </div>
            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">Rate</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $deliveryRate }}%</p>
        <p class="text-sm text-gray-500 mt-1">Delivery Rate</p>
    </div>
</div>

<!-- Second Row KPIs -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center space-x-4">
        <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-users text-purple-600 text-2xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalContacts) }}</p>
            <p class="text-sm text-gray-500">Total Contacts</p>
            <p class="text-xs text-green-600">{{ number_format($activeContacts) }} subscribed</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center space-x-4">
        <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-bullhorn text-blue-600 text-2xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalCampaigns }}</p>
            <p class="text-sm text-gray-500">Total Campaigns</p>
            <p class="text-xs text-yellow-600">{{ $activeCampaigns }} active</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center space-x-4">
        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-server text-green-600 text-2xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $activeSmtp }}</p>
            <p class="text-sm text-gray-500">Active SMTP Providers</p>
            <p class="text-xs text-gray-400">{{ $totalSmtp }} total configured</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- SMTP Usage -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-server mr-2 text-indigo-600"></i>SMTP Daily Usage</h3>
        @if($smtpUsage->isEmpty())
        <p class="text-gray-400 text-sm">No active SMTP providers configured.</p>
        @else
        <div class="space-y-4">
            @foreach($smtpUsage as $smtp)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $smtp->name }}</span>
                    <span class="text-xs text-gray-500">{{ number_format($smtp->daily_sent_count) }} / {{ number_format($smtp->max_daily_emails) }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php $pct = min(100, $smtp->usage_percent); @endphp
                    <div class="h-2 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-indigo-500') }}"
                        style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">{{ $pct }}% used &bull; {{ number_format($smtp->remaining_today) }} remaining</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Recent Campaigns -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-bullhorn mr-2 text-indigo-600"></i>Recent Campaigns</h3>
            <a href="{{ route('admin.campaigns.index') }}" class="text-indigo-600 text-sm hover:underline">View All</a>
        </div>
        @if($recentCampaigns->isEmpty())
        <p class="text-gray-400 text-sm">No campaigns yet.</p>
        @else
        <div class="space-y-3">
            @foreach($recentCampaigns as $campaign)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ Str::limit($campaign->name, 35) }}</p>
                    <p class="text-xs text-gray-400">{{ $campaign->created_at->diffForHumans() }}</p>
                </div>
                @php
                    $colors = ['draft'=>'gray','queued'=>'blue','sending'=>'yellow','completed'=>'green','paused'=>'orange','cancelled'=>'red'];
                    $color = $colors[$campaign->status] ?? 'gray';
                @endphp
                <span class="px-2 py-1 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $campaign->status }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<!-- Recent Email Log -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-clipboard-list mr-2 text-indigo-600"></i>Recent Email Activity</h3>
        <a href="{{ route('admin.logs.index') }}" class="text-indigo-600 text-sm hover:underline">View All Logs</a>
    </div>
    @if($recentLogs->isEmpty())
    <p class="text-gray-400 text-sm">No email activity yet.</p>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b">
                    <th class="pb-2 pr-4">Recipient</th>
                    <th class="pb-2 pr-4">Campaign</th>
                    <th class="pb-2 pr-4">SMTP</th>
                    <th class="pb-2 pr-4">Status</th>
                    <th class="pb-2">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentLogs as $log)
                <tr>
                    <td class="py-2 pr-4 text-gray-800">{{ $log->recipient_email }}</td>
                    <td class="py-2 pr-4 text-gray-600">{{ Str::limit($log->campaign->name ?? 'N/A', 25) }}</td>
                    <td class="py-2 pr-4 text-gray-500 text-xs">{{ $log->smtpProvider->name ?? 'N/A' }}</td>
                    <td class="py-2 pr-4">
                        @php
                            $sc = ['sent'=>'green','failed'=>'red','queued'=>'blue','sending'=>'yellow','cancelled'=>'gray'];
                            $sc2 = $sc[$log->status] ?? 'gray';
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $sc2 }}-100 text-{{ $sc2 }}-700 capitalize">{{ $log->status }}</span>
                    </td>
                    <td class="py-2 text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
