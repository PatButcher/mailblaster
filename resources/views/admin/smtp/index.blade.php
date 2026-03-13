@extends('layouts.admin')
@section('title', 'SMTP Providers - MailBlast')
@section('page-title', 'SMTP Providers')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-gray-500 text-sm">Manage your outbound email servers with round-robin rotation</p>
    </div>
    <a href="{{ route('admin.smtp.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Add SMTP Provider
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Provider</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Host / Port</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Daily Usage</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($providers as $provider)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-server text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $provider->name }}</p>
                            <p class="text-xs text-gray-400">From: {{ $provider->from_email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-700">{{ $provider->host }}</p>
                    <p class="text-xs text-gray-400">Port {{ $provider->port }} / {{ strtoupper($provider->encryption) }}</p>
                </td>
                <td class="px-6 py-4">
                    <div class="w-32">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">{{ number_format($provider->daily_sent_count) }}/{{ number_format($provider->max_daily_emails) }}</span>
                            <span class="text-gray-400">{{ $provider->usage_percent }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            @php $pct = min(100, $provider->usage_percent); @endphp
                            <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @if($provider->test_status)
                    <p class="text-xs mt-1 {{ $provider->test_status === 'success' ? 'text-green-600' : 'text-red-500' }}">
                        <i class="fas fa-{{ $provider->test_status === 'success' ? 'check' : 'times' }} mr-1"></i>
                        Last tested {{ $provider->last_tested_at?->diffForHumans() }}
                    </p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">#{{ $provider->priority }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full {{ $provider->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $provider->active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($provider->is_at_limit)
                    <span class="ml-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-600">At Limit</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-2">
                        <!-- Test SMTP -->
                        <button onclick="document.getElementById('test-modal-{{ $provider->id }}').classList.remove('hidden')" class="text-blue-600 hover:text-blue-800 text-sm" title="Test SMTP">
                            <i class="fas fa-vial"></i>
                        </button>
                        <!-- Reset Daily -->
                        <form action="{{ route('admin.smtp.reset-daily', $provider->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-sm" title="Reset Daily Count" onclick="return confirm('Reset daily count for {{ $provider->name }}?')">
                                <i class="fas fa-redo"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.smtp.edit', $provider->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.smtp.destroy', $provider->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm" title="Delete" onclick="return confirm('Delete this SMTP provider?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Test Modal -->
            <tr id="test-modal-{{ $provider->id }}" class="hidden bg-blue-50">
                <td colspan="6" class="px-6 py-4">
                    <form action="{{ route('admin.smtp.test', $provider->id) }}" method="POST" class="flex items-center space-x-3">
                        @csrf
                        <i class="fas fa-vial text-blue-600"></i>
                        <span class="text-sm text-blue-800 font-medium">Send test email via {{ $provider->name }}:</span>
                        <input type="email" name="test_email" placeholder="recipient@example.com" class="border border-blue-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded text-sm hover:bg-blue-700">Send Test</button>
                        <button type="button" onclick="document.getElementById('test-modal-{{ $provider->id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-sm">Cancel</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                    <i class="fas fa-server text-4xl mb-3 block"></i>
                    <p>No SMTP providers configured yet.</p>
                    <a href="{{ route('admin.smtp.create') }}" class="text-indigo-600 hover:underline text-sm mt-1 block">Add your first SMTP provider</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $providers->links() }}
    </div>
</div>
@endsection
