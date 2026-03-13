@extends('layouts.admin')
@section('title', 'Add SMTP Provider')
@section('page-title', 'Add SMTP Provider')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.smtp.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. SendGrid Primary" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host <span class="text-red-500">*</span></label>
                    <input type="text" name="host" value="{{ old('host') }}" placeholder="smtp.sendgrid.net" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('host') border-red-500 @enderror" required>
                    @error('host')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Port <span class="text-red-500">*</span></label>
                    <select name="port" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="587" {{ old('port', 587) == 587 ? 'selected' : '' }}>587 (TLS - Recommended)</option>
                        <option value="465" {{ old('port') == 465 ? 'selected' : '' }}>465 (SSL)</option>
                        <option value="25" {{ old('port') == 25 ? 'selected' : '' }}>25 (Default)</option>
                        <option value="2525" {{ old('port') == 2525 ? 'selected' : '' }}>2525 (Alternative)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encryption <span class="text-red-500">*</span></label>
                    <select name="encryption" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="tls" {{ old('encryption', 'tls') === 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                        <option value="ssl" {{ old('encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ old('encryption') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="apikey or email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('username') border-red-500 @enderror" required>
                    @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password / API Key <span class="text-red-500">*</span></label>
                    <input type="password" name="password" placeholder="Your SMTP password or API key" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror" required>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email <span class="text-red-500">*</span></label>
                    <input type="email" name="from_email" value="{{ old('from_email') }}" placeholder="noreply@yourcompany.com" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('from_email') border-red-500 @enderror" required>
                    @error('from_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Name <span class="text-red-500">*</span></label>
                    <input type="text" name="from_name" value="{{ old('from_name') }}" placeholder="Your Company" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('from_name') border-red-500 @enderror" required>
                    @error('from_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Daily Emails <span class="text-red-500">*</span></label>
                    <input type="number" name="max_daily_emails" value="{{ old('max_daily_emails', 500) }}" min="1" max="100000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <p class="text-xs text-gray-400 mt-1">Daily sending limit for this provider</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority <span class="text-red-500">*</span></label>
                    <input type="number" name="priority" value="{{ old('priority', 1) }}" min="1" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <p class="text-xs text-gray-400 mt-1">Lower number = used first in rotation</p>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Enable this SMTP provider for sending</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.smtp.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                    <i class="fas fa-plus mr-2"></i>Add Provider
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
