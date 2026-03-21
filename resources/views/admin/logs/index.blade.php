@extends('layouts.admin')
@section('title', 'Email Logs')
@section('page-title', 'Email Send Logs')

@section('content')
<!-- Today Stats -->
<div class="flex flex-wrap gap-3 mb-6">
    @foreach(['sent' => 'green', 'failed' => 'red', 'queued' => 'blue'] as $status => $color)
    <div class="bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-lg px-4 py-2">
        <span class="font-semibold text-{{ $color }}-700">{{ number_format($statsToday[$status] ?? 0) }}</span>
        <span class="text-{{ $color }}-600 text-sm ml-1 capitalize">{{ $status }} today</span>
    </div>
    @endforeach

    <!-- Export Dropdown -->
    <div class="ml-auto relative" x-data="{ open: false }">
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.logs.export') }}?{{ http_build_query(request()->only('status','campaign_id','date_from','date_to')) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-download mr-2"></i>Export Current View (CSV)
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Campaign</label>
            <select name="campaign_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Campaigns</option>
                @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach(['sent','failed','queued','sending','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="campaign" {{ request('type') === 'campaign' ? 'selected' : '' }}>Campaign</option>
                <option value="single" {{ request('type') === 'single' ? 'selected' : '' }}>Single Send</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search Email</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="recipient@example.com" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
        <a href="{{ route('admin.logs.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm">Clear</a>
    </form>
</div>

<!-- Clear Logs -->
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
    <form action="{{ route('admin.logs.clear') }}" method="POST" class="flex items-center space-x-3">
        @csrf
        <i class="fas fa-trash text-yellow-600"></i>
        <span class="text-sm text-yellow-800">Clear logs older than</span>
        <input type="number" name="older_than_days" value="30" min="1" max="365" class="border border-yellow-300 rounded px-2 py-1 text-sm w-16">
        <span class="text-sm text-yellow-800">days</span>
        <button type="submit" onclick="return confirm('Clear old logs?')" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">Clear</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recipient</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Campaign / Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SMTP</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
            @php
                $sc = ['sent'=>'green','failed'=>'red','queued'=>'blue','sending'=>'yellow','cancelled'=>'gray'];
                $sc2 = $sc[$log->status] ?? 'gray';
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-800">{{ $log->recipient_email }}</p>
                    @if($log->recipient_name)<p class="text-xs text-gray-400">{{ $log->recipient_name }}</p>@endif
                </td>
                <td class="px-5 py-3">
                    @if($log->is_single_send)
                    <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full">Single Send</span>
                    @else
                    <span class="text-gray-600 text-xs">{{ Str::limit($log->campaign->name ?? 'N/A', 28) }}</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $log->smtpProvider->name ?? 'N/A' }}</td>
                <td class="px-5 py-3">
                    @if($log->smtp_response_code)
                    <span class="font-mono text-xs {{ $log->smtp_response_code === '250' ? 'text-green-600' : 'text-red-600' }}">{{ $log->smtp_response_code }}</span>
                    @else
                    <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $sc2 }}-100 text-{{ $sc2 }}-700 capitalize">{{ $log->status }}</span>
                </td>

                
                {{-- <td class="px-5 py-3 text-gray-400 text-xs">{{ $log->sent_at->diffForHumans() }}</td> --}}
                <td class="px-5 py-3 text-gray-400 text-xs">{{ $log->sent_at->toDateTimeString() }}</td>
                {{-- <td class="px-5 py-3 text-gray-400 text-xs">{{ $sentAt }}</td> --}}
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('admin.logs.show', $log->id) }}" class="text-indigo-600 hover:text-indigo-800 mr-2 text-xs"><i class="fas fa-eye"></i></a>
                    @if($log->status === 'failed')
                    <form action="{{ route('admin.logs.retry', $log->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-800 text-xs" title="Retry"><i class="fas fa-redo"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No email logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
</div>
@endsection
