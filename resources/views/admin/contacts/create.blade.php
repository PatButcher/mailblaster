@extends('layouts.admin')
@section('title', 'Add Contact')
@section('page-title', 'Add Contact')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.contacts.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <input type="text" name="company" value="{{ old('company') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" placeholder="e.g. vip, enterprise, newsletter" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Separate multiple tags with commas</p>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="subscribed" value="1" {{ old('subscribed', true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Subscribed to emails</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.contacts.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700"><i class="fas fa-plus mr-2"></i>Add Contact</button>
            </div>
        </form>
    </div>
</div>
@endsection
