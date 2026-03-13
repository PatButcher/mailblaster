@extends('layouts.admin')
@section('title', 'Log Details')
@section('page-title', 'Email Log Details')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-6">
        @php
            $sc  = ['sent'=>'green','failed'=>'red','queued'=>'blue','sending'=>'yellow','cancelled'=>'gray'];
            $sc2 = $sc[$log->status] ?? 'gray';
        @endphp
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Email Log #{{ $log->id }}</h3>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 text-sm rounded-full bg-{{ $sc2 }}-100 text-{{ $sc2 }}-700 capitalize">{{ $log->status }}</span>
                @if($log->is_single_send)
                <span class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-700">Single Send</span>
                @endif
            </div>
        </div>
        <dl class="space-y-4 text-sm">
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Recipient</dt>
                <dd class="col-span-2 text-gray-800">{{ $log->recipient_email }}{{ $log->recipient_name ? ' (' . $log->recipient_name . ')' : '' }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Subject</dt>
                <dd class="col-span-2 text-gray-800">{{ $log->subject ?? ($log->campaign->subject ?? 'N/A') }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Campaign</dt>
                <dd class="col-span-2 text-gray-800">
                    @if($log->campaign)
                    <a href="{{ route('admin.campaigns.show', $log->campaign_id) }}" class="text-indigo-600 hover:underline">{{ $log->campaign->name }}</a>
                    @else
                    <span class="text-gray-400">Single Send (no campaign)</span>
                    @endif
                </dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">SMTP Provider</dt>
                <dd class="col-span-2 text-gray-800">{{ $log->smtpProvider->name ?? 'Not assigned' }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Message ID</dt>
                <dd class="col-span-2 text-gray-600 font-mono text-xs">{{ $log->message_id ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">SMTP Response</dt>
                <dd class="col-span-2">
                    @if($log->smtp_response_code)
                    <span class="font-mono text-xs bg-green-100 text-green-800 px-2 py-1 rounded">{{ $log->smtp_response_code }}</span>
                    @if($log->smtp_banner)
                    <span class="ml-2 text-xs text-gray-500">{{ $log->smtp_banner }}</span>
                    @endif
                    @else
                    <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Attempts</dt>
                <dd class="col-span-2 text-gray-800">{{ $log->attempts }}</dd>
            </div>
            @if($log->sent_at)
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Sent At</dt>
                <dd class="col-span-2 text-green-700">{{ $log->sent_at->format('M d, Y H:i:s') }}</dd>
            </div>
            @endif
            @if($log->failed_at)
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Failed At</dt>
                <dd class="col-span-2 text-red-600">{{ $log->failed_at->format('M d, Y H:i:s') }}</dd>
            </div>
            @endif
            @if($log->error_message)
            <div class="grid grid-cols-3 gap-4 py-3 border-b border-gray-100">
                <dt class="font-medium text-gray-500">Error</dt>
                <dd class="col-span-2"><div class="bg-red-50 border border-red-200 rounded p-3 text-xs text-red-700 font-mono">{{ $log->error_message }}</div></dd>
            </div>
            @endif
        </dl>
        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.logs.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm"><i class="fas fa-arrow-left mr-2"></i>Back</a>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.logs.export') }}?campaign_id={{ $log->campaign_id }}" class="border border-indigo-300 text-indigo-600 px-4 py-2 rounded-lg text-sm hover:bg-indigo-50">
                    <i class="fas fa-download mr-2"></i>Export Campaign Logs
                </a>
                @if($log->status === 'failed')
                <form action="{{ route('admin.logs.retry', $log->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                        <i class="fas fa-redo mr-2"></i>Retry Send
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- SMTP Debug Log -->
    @if($log->smtp_log)
    <div class="bg-gray-900 rounded-xl shadow-sm border border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
            <div class="flex items-center space-x-2">
                <div class="flex space-x-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
                <span class="text-gray-400 text-sm font-mono ml-2">SMTP Transaction Log</span>
            </div>
        </div>
        <div class="font-mono text-xs p-4 max-h-80 overflow-y-auto space-y-0.5">
            @foreach(explode("\n", $log->smtp_log) as $line)
            @php
                $lc = trim($line);
                $lineClass = 'text-gray-400';
                if (str_contains($lc, '✓') || str_contains($lc, '250') || str_contains($lc, 'OK')) $lineClass = 'text-green-400';
                elseif (str_contains($lc, '✗') || str_contains($lc, 'ERROR') || str_contains($lc, '5')) $lineClass = 'text-red-400';
                elseif (str_contains($lc, '[SEND]')) $lineClass = 'text-blue-400';
                elseif (str_contains($lc, '[RECV]')) $lineClass = 'text-yellow-300';
                elseif (str_contains($lc, '[TLS]') || str_contains($lc, '[CONNECT]')) $lineClass = 'text-purple-400';
            @endphp
            <div class="{{ $lineClass }}">{{ $line }}</div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
