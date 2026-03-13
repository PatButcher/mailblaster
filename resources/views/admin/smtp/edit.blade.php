@extends('layouts.admin')
@section('title', 'Edit SMTP Provider')
@section('page-title', 'Edit SMTP Provider')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.smtp.update', $provider->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider Name</label>
                    <input type="text" name="name" value="{{ old('name', $provider->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                    <input type="text" name="host" value="{{ old('host', $provider->host) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Port</label>
                    <select name="port" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="587" {{ old('port', $provider->port) == 587 ? 'selected' : '' }}>587 (TLS)</option>
                        <option value="465" {{ old('port', $provider->port) == 465 ? 'selected' : '' }}>465 (SSL)</option>
                        <option value="25" {{ old('port', $provider->port) == 25 ? 'selected' : '' }}>25 (Default)</option>
                        <option value="2525" {{ old('port', $provider->port) == 2525 ? 'selected' : '' }}>2525 (Alt)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                    <select name="encryption" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="tls" {{ old('encryption', $provider->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('encryption', $provider->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ old('encryption', $provider->encryption) === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" value="{{ old('username', $provider->username) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password / API Key</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep the existing password</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                    <input type="email" name="from_email" value="{{ old('from_email', $provider->from_email) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name', $provider->from_name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Daily Emails</label>
                    <input type="number" name="max_daily_emails" value="{{ old('max_daily_emails', $provider->max_daily_emails) }}" min="1" max="100000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <input type="number" name="priority" value="{{ old('priority', $provider->priority) }}" min="1" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="active" value="1" {{ old('active', $provider->active) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Enable this SMTP provider</span>
                    </label>
                </div>
                <div class="md:col-span-2 bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600"><span class="font-medium">Current Stats:</span> {{ number_format($provider->daily_sent_count) }} sent today / {{ number_format($provider->total_sent_count) }} total sent / {{ number_format($provider->failed_count) }} failed</p>
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.smtp.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
