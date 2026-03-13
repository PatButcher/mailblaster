@extends('layouts.admin')
@section('title', 'Reschedule Campaign')
@section('page-title', 'Reschedule Campaign')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
            <p class="text-sm font-semibold text-indigo-800">{{ $campaign->name }}</p>
            <p class="text-xs text-indigo-600 mt-1">Current status: <span class="capitalize font-medium">{{ $campaign->status }}</span></p>
            @if($campaign->total_recipients > 0)
            <p class="text-xs text-indigo-600">Previously sent to {{ number_format($campaign->total_recipients) }} recipients</p>
            @endif
        </div>

        <div class="mb-5 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800"><i class="fas fa-exclamation-triangle mr-2"></i>Rescheduling will reset the campaign to Draft status and clear failed/cancelled email logs. Previously sent emails will not be re-sent.</p>
        </div>

        <form action="{{ route('admin.campaigns.reschedule.post', $campaign->id) }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Scheduled Date & Time <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="scheduled_at"
                    min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                    value="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                @error('scheduled_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                    <i class="fas fa-calendar mr-2"></i>Reschedule Campaign
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
