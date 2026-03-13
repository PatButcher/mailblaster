@extends('layouts.admin')
@section('title', 'New Campaign')
@section('page-title', 'Create New Campaign')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.campaigns.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div class="pb-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-info-circle text-indigo-600 mr-2"></i>Campaign Details</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Q2 Newsletter - Product Updates" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror" required>
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Subject <span class="text-red-500">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject line - use @{{first_name}} for personalization" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                    </div>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-user text-indigo-600 mr-2"></i>Sender Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Name <span class="text-red-500">*</span></label>
                            <input type="text" name="from_name" value="{{ old('from_name') }}" placeholder="Your Company" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Email <span class="text-red-500">*</span></label>
                            <input type="email" name="from_email" value="{{ old('from_email') }}" placeholder="noreply@yourcompany.com" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reply-To Email</label>
                            <input type="email" name="reply_to" value="{{ old('reply_to') }}" placeholder="Optional - defaults to from email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-users text-indigo-600 mr-2"></i>Recipients ({{ number_format($contactCount) }} subscribed contacts)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Filter</label>
                            <select name="recipient_filter" id="recipient_filter" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="document.getElementById('tags-filter').style.display=this.value==='tagged'?'block':'none'">
                                <option value="subscribed" {{ old('recipient_filter', 'subscribed') === 'subscribed' ? 'selected' : '' }}>All Subscribed Contacts</option>
                                <option value="tagged" {{ old('recipient_filter') === 'tagged' ? 'selected' : '' }}>By Tag</option>
                                <option value="all" {{ old('recipient_filter') === 'all' ? 'selected' : '' }}>All Contacts (including unsubscribed)</option>
                            </select>
                        </div>
                        <div id="tags-filter" style="display:{{ old('recipient_filter') === 'tagged' ? 'block' : 'none' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Tags</label>
                            <input type="text" name="tags_filter" value="{{ old('tags_filter') }}" placeholder="e.g. vip, enterprise" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-code text-indigo-600 mr-2"></i>Email Content</h3>
                    <div class="mb-2 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <p class="text-xs text-indigo-700"><strong>Personalization tags:</strong> Use <code class="bg-indigo-100 px-1 rounded">@{{first_name}}</code>, <code class="bg-indigo-100 px-1 rounded">@{{last_name}}</code>, <code class="bg-indigo-100 px-1 rounded">@{{full_name}}</code>, <code class="bg-indigo-100 px-1 rounded">@{{email}}</code> in your content</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">HTML Email Body <span class="text-red-500">*</span></label>
                        <textarea name="body_html" rows="10" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>{{ old('body_html') }}
                        </textarea>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Plain Text Version</label>
                        <textarea name="body_text" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Plain text fallback for email clients that don't support HTML">{{ old('body_text') }}</textarea>
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-cog text-indigo-600 mr-2"></i>Sending Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Batch Size</label>
                            <input type="number" name="batch_size" value="{{ old('batch_size', 280) }}" min="1" max="500000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Emails per processing batch</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delay Between Batches (seconds)</label>
                            <input type="number" name="delay_between_batches" value="{{ old('delay_between_batches', 88000) }}" min="0" max="88000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_recurring" value="1" class="form-checkbox h-5 w-5 text-indigo-600" {{ old('is_recurring') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Repeat campaign indefinitely after completion</span>
                            </label>
                            <p class="text-xs text-gray-400 mt-1">If enabled, this campaign will automatically restart once all recipients have been mailed.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Send (Optional)</label>
                            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Leave blank to send immediately when you click Send</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.campaigns.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i>Save as Draft
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
