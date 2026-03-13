@extends('layouts.admin')
@section('title', 'Edit Campaign')
@section('page-title', 'Edit Campaign')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.campaigns.update', $campaign->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Name</label>
                    <input type="text" name="name" value="{{ old('name', $campaign->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Subject</label>
                    <input type="text" name="subject" value="{{ old('subject', $campaign->subject) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                        <input type="text" name="from_name" value="{{ old('from_name', $campaign->from_name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                        <input type="email" name="from_email" value="{{ old('from_email', $campaign->from_email) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reply-To</label>
                        <input type="email" name="reply_to" value="{{ old('reply_to', $campaign->reply_to) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Filter</label>
                        <select name="recipient_filter" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="subscribed" {{ old('recipient_filter', $campaign->recipient_filter) === 'subscribed' ? 'selected' : '' }}>All Subscribed</option>
                            <option value="tagged" {{ old('recipient_filter', $campaign->recipient_filter) === 'tagged' ? 'selected' : '' }}>By Tag</option>
                            <option value="all" {{ old('recipient_filter', $campaign->recipient_filter) === 'all' ? 'selected' : '' }}>All Contacts</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">HTML Body</label>
                    <textarea name="body_html" rows="10" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>{{ old('body_html', $campaign->body_html) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plain Text Version</label>
                    <textarea name="body_text" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('body_text', $campaign->body_text) }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_recurring" value="1" class="form-checkbox h-5 w-5 text-indigo-600" {{ old('is_recurring', $campaign->is_recurring) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Repeat campaign indefinitely after completion</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1">If enabled, this campaign will automatically restart once all recipients have been mailed.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Batch Size</label>
                        <input type="number" name="batch_size" value="{{ old('batch_size', $campaign->batch_size) }}" min="1" max="500000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delay Between Batches (sec)</label>
                        <input type="number" name="delay_between_batches" value="{{ old('delay_between_batches', $campaign->delay_between_batches) }}" min="0" max="88000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700"><i class="fas fa-save mr-2"></i>Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
