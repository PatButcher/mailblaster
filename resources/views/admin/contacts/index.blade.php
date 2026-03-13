@extends('layouts.admin')
@section('title', 'Contacts - MailBlast')
@section('page-title', 'Contacts')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center space-x-3">
        <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-sm">
            <span class="font-semibold text-gray-800">{{ number_format($totalContacts) }}</span>
            <span class="text-gray-500 ml-1">Total</span>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm">
            <span class="font-semibold text-green-700">{{ number_format($subscribedCount) }}</span>
            <span class="text-green-600 ml-1">Subscribed</span>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm">
            <span class="font-semibold text-red-700">{{ number_format($unsubscribedCount) }}</span>
            <span class="text-red-600 ml-1">Unsubscribed</span>
        </div>
    </div>
    <div class="flex items-center space-x-2">
        <a href="{{ route('admin.contacts.export') }}" class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fas fa-download mr-2"></i>Export CSV
        </a>
        <a href="{{ route('admin.contacts.import') }}" class="border border-indigo-300 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fas fa-upload mr-2"></i>Import CSV
        </a>
        <a href="{{ route('admin.contacts.generate.form') }}" class="border border-purple-300 text-purple-600 hover:bg-purple-50 px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fas fa-magic mr-2"></i>Generate Contacts
        </a>
        <a href="{{ route('admin.contacts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>Add Contact
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" action="{{ route('admin.contacts.index') }}" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="flex-1 min-w-48 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="subscribed" {{ request('status') === 'subscribed' ? 'selected' : '' }}>Subscribed</option>
            <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
        </select>
        <select name="per_page" id="perPageSelector" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @foreach ([25, 50, 100, 500, 1000, 10000] as $value)
                <option value="{{ $value }}" {{ request('per_page', 1000) == $value ? 'selected' : '' }}>{{ $value }} per page</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
        <a href="{{ route('admin.contacts.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm">Clear</a>
    </form>
</div>

<form id="contactsForm" action="{{ route('admin.contacts.bulk-delete') }}" method="POST">
    @csrf
    @method('DELETE')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-10 px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        <input type="checkbox" id="selectAllContacts" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tags</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contacts as $contact)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="ids[]" value="{{ $contact->id }}" class="contact-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-700 text-sm font-semibold">{{ strtoupper(substr($contact->first_name ?: $contact->email, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $contact->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $contact->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $contact->company ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @foreach($contact->tags_array as $tag)
                        <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full mr-1">{{ $tag }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $contact->source) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $contact->subscribed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $contact->subscribed ? 'Subscribed' : 'Unsubscribed' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.contacts.edit', $contact->id) }}" class="text-indigo-600 hover:text-indigo-800 mr-3 text-sm"><i class="fas fa-edit"></i></a>
                        <button type="button" class="text-red-500 hover:text-red-700 text-sm delete-single-contact" data-id="{{ $contact->id }}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-users text-4xl mb-3 block"></i>
                        <p>No contacts found.</p>
                        <a href="{{ route('admin.contacts.import') }}" class="text-indigo-600 hover:underline text-sm mt-1 block">Import contacts from CSV</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div>
                <button type="submit" id="bulkDeleteBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50" disabled onclick="return confirm('Are you sure you want to delete selected contacts?');">
                    <i class="fas fa-trash mr-2"></i>Delete Selected
                </button>
            </div>
            <div>{{ $contacts->links() }}</div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllContacts');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const perPageSelector = document.getElementById('perPageSelector');

    function updateSelectAllCheckboxState() {
        const allContactCheckboxes = document.querySelectorAll('.contact-checkbox');
        const checkedContactCheckboxes = document.querySelectorAll('.contact-checkbox:checked');
        selectAllCheckbox.checked = allContactCheckboxes.length > 0 && checkedContactCheckboxes.length === allContactCheckboxes.length;
    }

    function toggleBulkDeleteButton() {
        const checkedCheckboxes = document.querySelectorAll('.contact-checkbox:checked');
        bulkDeleteBtn.disabled = checkedCheckboxes.length === 0;
    }

    selectAllCheckbox.addEventListener('change', function() {
        document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        toggleBulkDeleteButton();
    });

    // Attach listeners to existing contact checkboxes
    document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckboxState(); // Update master checkbox state
            toggleBulkDeleteButton();
        });
    });

    perPageSelector.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        window.location.href = url.toString();
    });

    // Handle single contact delete (already existing form)
    document.querySelectorAll('.delete-single-contact').forEach(button => {
        button.addEventListener('click', function() {
            const contactId = this.dataset.id;
            if (confirm('Are you sure you want to delete this contact?')) {
                const form = document.createElement('form');
                form.action = `/admin/contacts/${contactId}`; // Adjust this route as needed
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="_method" value="DELETE">
                                  <input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Initial checks on page load
    updateSelectAllCheckboxState(); // Set initial state of selectAllCheckbox
    toggleBulkDeleteButton(); // Initial check for bulk delete button
});
</script>
@endpush



