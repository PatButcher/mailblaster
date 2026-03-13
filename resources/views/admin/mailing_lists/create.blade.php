@extends('layouts.admin')
@section('title', 'Create Mailing List')
@section('page-title', 'Create New Mailing List')

@section('content')
<div class="max-w-xl">
    <div class="bg-white shadow-sm rounded-lg p-6">
        <form action="{{ route('admin.mailing_lists.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">List Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <a href="{{ route('admin.mailing_lists.index') }}" class="btn-secondary mr-2">Cancel</a>
                <button type="submit" class="btn-primary">Create List</button>
            </div>
        </form>
    </div>
</div>
@endsection