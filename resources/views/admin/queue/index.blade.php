@extends('layouts.admin')
@section('title', 'Send Queue')
@section('page-title', 'Email Send Queue')

@section('content')
<!-- Queue Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-center">
        <p class="text-3xl font-bold text-blue-700">{{ number_format($queueStats['queued']) }}</p>
        <p class="text-sm text-blue-600 mt-1">Queued</p>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
        <p class="text-3xl font-bold text-yellow-700">{{ number_format($queueStats['sending']) }}</p>
        <p class="text-sm text-yellow-600 mt-1">Sending</p>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
        <p class="text-3xl font-bold text-green-700">{{ number_format($queueStats['sent']) }}</p>
        <p class="text-sm text-green-600 mt-1">Sent Today</p>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
        <p class="text-3xl font-bold text-red-700">{{ number_format($queueStats['failed']) }}</p>
        <p class="text-sm text-red-600 mt-1">Failed Today</p>
    </div>
</div>

<!-- Process Queue Controls -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-play-circle text-indigo-600 mr-2"></i>Queue Processing</h3>
    <div class="flex flex-wrap gap-4 items-center">
        <form action="{{ route('admin.queue.process') }}" method="POST" class="flex items-center space-x-3">
            @csrf
            <label for="batch_limit" class="text-sm text-gray-600">Process</label>
            <input type="number" name="batch_limit" id="batch_limit" value="50" min="1" max="500000" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24">
            <label class="text-sm text-gray-600">emails now</label>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-bolt mr-2"></i>Process Queue
            </button>
        </form>
        <form action="{{ route('admin.queue.clear-failed') }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Clear all failed email logs?')" class="border border-red-300 text-red-600 hover:bg-red-50 px-5 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-trash mr-2"></i>Clear Failed Logs
            </button>
        </form>
    </div>
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-blue-700"><i class="fas fa-info-circle mr-2"></i>Click "Process Queue" to manually trigger sending of queued emails. For automated sending, run: <code class="bg-blue-100 px-1 rounded">php artisan queue:work</code> or set up a cron job.</p>
    </div>

    <h3 class="text-base font-semibold text-gray-800 mb-4 mt-6"><i class="fas fa-eraser text-red-600 mr-2"></i>Delete Records by ID Range</h3>
    <form action="{{ route('admin.queue.deleteByRange') }}" method="POST" class="flex flex-wrap gap-4 items-center">
        @csrf
        <label for="start_id" class="text-sm text-gray-600">Start ID:</label>
        <input type="number" name="start_id" id="start_id" min="1" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24" required>
        <label for="end_id" class="text-sm text-gray-600">End ID:</label>
        <input type="number" name="end_id" id="end_id" min="1" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24" required>
        <button type="submit" onclick="return confirm('Are you sure you want to delete email logs in this ID range? This action cannot be undone.')" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
            <i class="fas fa-times-circle mr-2"></i>Delete Range
        </button>
    </form>
</div>

<!-- SMTP Provider Status -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-server text-indigo-600 mr-2"></i>SMTP Provider Rotation Status</h3>
    @if($smtpStatus->isEmpty())
    <p class="text-gray-400 text-sm">No active SMTP providers. <a href="{{ route('admin.smtp.create') }}" class="text-indigo-600 hover:underline">Add one now</a>.</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($smtpStatus as $smtp)
        <div class="border {{ $smtp->is_at_limit ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-gray-50' }} rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="font-medium text-sm text-gray-800">{{ $smtp->name }}</p>
                <span class="text-xs {{ $smtp->is_at_limit ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100' }} px-2 py-0.5 rounded-full">
                    {{ $smtp->is_at_limit ? 'At Limit' : 'Available' }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                @php $pct = min(100, $smtp->usage_percent); @endphp
                <div class="h-2 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <span>Sent: {{ number_format($smtp->daily_sent_count) }}/{{ number_format($smtp->max_daily_emails) }}</span>
                <span>Remaining: {{ number_format($smtp->remaining_today) }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Priority #{{ $smtp->priority }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Queued Emails -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800">Queued Emails</h3>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">{{ number_format($queueStats['queued']) }} emails waiting</span>
            <form id="perPageForm" method="GET" action="{{ route('admin.queue.index') }}" class="inline-flex items-center">
                <label for="per_page" class="text-sm text-gray-600 mr-1">Per page:</label>
                <select name="per_page" id="per_page" class="border border-gray-300 rounded-lg px-2 py-1 text-sm" onchange="document.getElementById('perPageForm').submit()">
                    @foreach([10, 20, 30, 50, 100, 1000, 10000] as $option)
                        <option value="{{ $option }}" @if($option == $perPage) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recipient</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Campaign</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Queued At</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Attempts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($queuedEmails as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-600">{{ $log->id }}</td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800">{{ $log->recipient_email }}</p>
                        @if($log->recipient_name)<p class="text-xs text-gray-400">{{ $log->recipient_name }}</p>@endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ Str::limit($log->campaign->name ?? 'N/A', 40) }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $log->attempts }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400"><i class="fas fa-check-circle text-green-400 text-3xl mb-2 block"></i>Queue is empty!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $queuedEmails->links() }}</div>
</div>
@endsection
