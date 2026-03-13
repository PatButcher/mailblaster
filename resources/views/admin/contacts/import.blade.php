@extends('layouts.admin')
@section('title', 'Import Contacts')
@section('page-title', 'Import Contacts from CSV')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">CSV File Requirements</h3>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800 font-medium mb-2"><i class="fas fa-info-circle mr-2"></i>Your CSV file must include the following columns:</p>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><code class="bg-blue-100 px-1 rounded">email</code> <span class="text-red-600">(required)</span> - Contact email address</li>
                    <li><code class="bg-blue-100 px-1 rounded">first_name</code> - Contact first name</li>
                    <li><code class="bg-blue-100 px-1 rounded">last_name</code> - Contact last name</li>
                    <li><code class="bg-blue-100 px-1 rounded">company</code> - Company name</li>
                    <li><code class="bg-blue-100 px-1 rounded">phone</code> - Phone number</li>
                    <li><code class="bg-blue-100 px-1 rounded">tags</code> - Comma-separated tags</li>
                </ul>
            </div>
        </div>

        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-xs font-medium text-gray-600 mb-2">Example CSV format:</p>
            <code class="text-xs text-gray-700 block">email,first_name,last_name,company,tags<br>john@example.com,John,Doe,Acme Corp,"vip,enterprise"<br>jane@example.com,Jane,Smith,Tech Co,newsletter</code>
        </div>

        <form action="{{ route('admin.contacts.import.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV File <span class="text-red-500">*</span></label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors">
                    <i class="fas fa-file-csv text-4xl text-gray-400 mb-3 block"></i>
                    <p class="text-gray-500 text-sm mb-2">Click to select or drag and drop your CSV file</p>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="block mx-auto text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                    <p class="text-xs text-gray-400 mt-2">Maximum file size: 10MB</p>
                </div>
                @error('csv_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800"><i class="fas fa-exclamation-triangle mr-2"></i>Duplicate emails will be automatically skipped. Imported contacts will be set as subscribed by default.</p>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.contacts.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                    <i class="fas fa-upload mr-2"></i>Import Contacts
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
