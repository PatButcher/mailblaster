@extends('layouts.admin')
@section('title', 'Campaign Details')
@section('page-title', 'Campaign: ' . $campaign->name)

@section('content')
@php
    $colors = ['draft'=>'gray','queued'=>'blue','sending'=>'yellow','completed'=>'green','paused'=>'orange','cancelled'=>'red'];
    $color = $colors[$campaign->status] ?? 'gray';
@endphp

<!-- Header Actions -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <span class="px-3 py-1 text-sm rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $campaign->status }}</span>
    @if(in_array($campaign->status, ['draft', 'paused']))
    <form action="{{ route('admin.campaigns.send', $campaign->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" onclick="return confirm('Queue this campaign for sending?')" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-paper-plane mr-2"></i>Send Campaign</button>
    </form>
    @endif
    @if(in_array($campaign->status, ['queued', 'sending']))
    <form action="{{ route('admin.campaigns.pause', $campaign->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-pause mr-2"></i>Pause</button>
    </form>
    @endif
    @if(!in_array($campaign->status, ['completed', 'cancelled']))
    <form action="{{ route('admin.campaigns.cancel', $campaign->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" onclick="return confirm('Cancel this campaign?')" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-ban mr-2"></i>Cancel</button>
    </form>
    @endif
    @if($campaign->queued_count > 0)
    <form action="{{ route('admin.campaigns.clearQueued', $campaign->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" onclick="return confirm('Are you sure you want to clear all queued emails for this campaign? This cannot be undone.')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-trash mr-2"></i>Clear Queued</button>
    </form>
    @endif
    
    {{-- Options for Cancelled Campaigns --}}
    @if($campaign->status === 'cancelled')
    <a href="{{ route('admin.campaigns.rescheduleForm', $campaign->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-sync-alt mr-2"></i>Reschedule</a>
    <form action="{{ route('admin.campaigns.duplicate', $campaign->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" onclick="return confirm('Duplicate this campaign?')" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-copy mr-2"></i>Duplicate</button>
    </form>
    @endif

    <a href="{{ route('admin.campaigns.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm"><i class="fas fa-arrow-left mr-2"></i>Back</a>
</div>

<!-- Stats Row -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-2xl font-bold text-gray-800">{{ number_format($campaign->total_recipients) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Recipients</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-2xl font-bold text-green-600">{{ number_format($campaign->sent_count ?? 0) }}</p>
        <p class="text-xs text-gray-500 mt-1">Sent</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-2xl font-bold text-red-500">{{ number_format($campaign->failed_count ?? 0) }}</p>
        <p class="text-xs text-gray-500 mt-1">Failed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ number_format($campaign->queued_count ?? 0) }}</p>
        <p class="text-xs text-gray-500 mt-1">Queued</p>
    </div>
</div>

@if($campaign->total_recipients > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    @php $progress = $campaign->progress_percent; @endphp
    <div class="flex justify-between text-sm text-gray-600 mb-2">
        <span class="font-medium">Send Progress</span>
        <span>{{ $progress }}% complete</span>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-3">
        <div class="bg-indigo-500 h-3 rounded-full transition-all" style="width: {{ $progress }}%"></div>
    </div>
</div>
@endif

<!-- Campaign Details -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Campaign Details</h3>
        <dl class="space-y-3 text-sm">
            <div class="flex"><dt class="w-32 text-gray-500">Subject</dt><dd class="flex-1 text-gray-800 font-medium">{{ $campaign->subject }}</dd></div>
            <div class="flex"><dt class="w-32 text-gray-500">From</dt><dd class="flex-1 text-gray-800">{{ $campaign->from_name }} &lt;{{ $campaign->from_email }}&gt;</dd></div>
            <div class="flex"><dt class="w-32 text-gray-500">Reply-To</dt><dd class="flex-1 text-gray-800">{{ $campaign->reply_to ?: '-' }}</dd></div>
            <div class="flex"><dt class="w-32 text-gray-500">Filter</dt><dd class="flex-1 text-gray-800 capitalize">{{ $campaign->recipient_filter }}{{ $campaign->tags_filter ? ': ' . $campaign->tags_filter : '' }}</dd></div>
            <div class="flex"><dt class="w-32 text-gray-500">Batch Size</dt><dd class="flex-1 text-gray-800">{{ $campaign->batch_size }} emails</dd></div>
            <div class="flex"><dt class="w-32 text-gray-500">Created</dt><dd class="flex-1 text-gray-800">{{ $campaign->created_at->format('M d, Y H:i') }} by {{ $campaign->created_by }}</dd></div>
            @if($campaign->started_at)<div class="flex"><dt class="w-32 text-gray-500">Started</dt><dd class="flex-1 text-gray-800">{{ $campaign->started_at->format('M d, Y H:i') }}</dd></div>@endif
            @if($campaign->completed_at)<div class="flex"><dt class="w-32 text-gray-500">Completed</dt><dd class="flex-1 text-gray-800">{{ $campaign->completed_at->format('M d, Y H:i') }}</dd></div>@endif
            <div class="flex items-center">
                <dt class="w-32 text-gray-500">Recurring</dt>
                <dd class="flex-1 text-gray-800">
                    <form action="{{ route('admin.campaigns.update', $campaign->id) }}" method="POST" class="inline" id="isRecurringForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $campaign->name }}"> {{-- Include existing fields to avoid validation errors --}}
                        <input type="hidden" name="subject" value="{{ $campaign->subject }}">
                        <input type="hidden" name="from_name" value="{{ $campaign->from_name }}">
                        <input type="hidden" name="from_email" value="{{ $campaign->from_email }}">
                        <input type="hidden" name="body_html" value="{{ $campaign->body_html }}">
                        <input type="hidden" name="recipient_filter" value="{{ $campaign->recipient_filter }}">
                        <input type="hidden" name="batch_size" value="{{ $campaign->batch_size }}">
                        <input type="hidden" name="delay_between_batches" value="{{ $campaign->delay_between_batches }}">
                        
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_recurring" value="1" class="sr-only peer" {{ $campaign->is_recurring ? 'checked' : '' }} onchange="document.getElementById('isRecurringForm').submit()">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $campaign->is_recurring ? 'Yes' : 'No' }}</span>
                        </label>
                    </form>
                </dd>
            </div>
        </dl>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">SMTP Usage</h3>
        @if($smtpStats->isEmpty())
        <p class="text-gray-400 text-sm">No SMTP usage data yet.</p>
        @else
        <div class="space-y-3">
            @foreach($smtpStats->groupBy('smtp_provider_id') as $providerId => $stats)
            @php $providerName = $stats->first()->smtpProvider->name ?? 'Unknown'; @endphp
            <div>
                <p class="text-sm font-medium text-gray-700 mb-1">{{ $providerName }}</p>
                @foreach($stats as $stat)
                <div class="flex justify-between text-xs text-gray-500">
                    <span class="capitalize">{{ $stat->status }}</span>
                    <span>{{ number_format($stat->count) }}</span>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<!-- Email Logs Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-5 border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-800">Email Send Log</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recipient</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SMTP Provider</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Attempts</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Error</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($logs as $log)
                @php
                    $sc = ['sent'=>'green','failed'=>'red','queued'=>'blue','sending'=>'yellow','cancelled'=>'gray'];
                    $sc2 = $sc[$log->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $log->recipient_email }}</p>
                        @if($log->recipient_name)<p class="text-xs text-gray-400">{{ $log->recipient_name }}</p>@endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $log->smtpProvider->name ?? 'N/A' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $sc2 }}-100 text-{{ $sc2 }}-700 capitalize">{{ $log->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $log->attempts }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $log->sent_at ? $log->sent_at->format('M d H:i') : ($log->created_at->format('M d H:i')) }}</td>
                    <td class="px-5 py-3 text-red-500 text-xs">{{ $log->error_message ? Str::limit($log->error_message, 60) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
</div>
@endsection