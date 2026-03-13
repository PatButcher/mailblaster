<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use App\Jobs\GenerateContactsJob; // Import the new job

class ContactController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $query = Contact::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('subscribed', $request->status === 'subscribed');
        }

        $perPage = $request->input('per_page', 1000); // Get per_page from request, default to 50
        if (!in_array($perPage, [25, 50, 100, 500, 1000, 10000])) {
            $perPage = 1000; // Ensure valid per_page value
        }

        $contacts = $query->orderBy('email', 'asc')->paginate($perPage)->withQueryString();
        $totalContacts = Contact::count();
        $subscribedCount = Contact::where('subscribed', true)->count();
        $unsubscribedCount = Contact::where('subscribed', false)->count();

        return view('admin.contacts.index', compact('contacts', 'totalContacts', 'subscribedCount', 'unsubscribedCount'));
    }

    public function create()
    {
        if ($r = $this->authCheck()) return $r;
        return view('admin.contacts.create');
    }

    public function store(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $validated = $request->validate([
            'email' => 'required|email|unique:contacts,email',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:500',
            'subscribed' => 'boolean'
        ]);

        $validated['subscribed'] = $request->has('subscribed');
        Contact::create($validated);

        return redirect()->route('admin.contacts.index')->with('success', 'Contact added successfully.');
    }

    public function edit($id)
    {
        if ($r = $this->authCheck()) return $r;
        $contact = Contact::findOrFail($id);
        return view('admin.contacts.edit', compact('contact'));
    }

    public function update(Request $request, $id)
    {
        if ($r = $this->authCheck()) return $r;

        $contact = Contact::findOrFail($id);
        $validated = $request->validate([
            'email' => 'required|email|unique:contacts,email,' . $id,
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:500',
        ]);

        $validated['subscribed'] = $request->has('subscribed');
        $contact->update($validated);

        return redirect()->route('admin.contacts.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy($id)
    {
        if ($r = $this->authCheck()) return $r;
        Contact::findOrFail($id)->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Contact deleted.');
    }

    public function bulkDelete(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $ids = $request->ids;
        $chunkSize = 999;
        $deletedCount = 0;

        foreach (array_chunk($ids, $chunkSize) as $idChunk) {
            $deletedCount += Contact::whereIn('id', $idChunk)->delete();
        }

        return back()->with('success', $deletedCount . ' contacts deleted.');
    }

    public function importForm()
    {
        if ($r = $this->authCheck()) return $r;
        return view('admin.contacts.import');
    }

    public function import(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            $handle = fopen($path, 'r');
            $header = fgetcsv($handle);
            $header = array_map('strtolower', array_map('trim', $header));

            $emailIndex = array_search('email', $header);
            if ($emailIndex === false) {
                return back()->with('error', 'CSV must contain an "email" column.');
            }

            $firstNameIndex = array_search('first_name', $header) ?? array_search('firstname', $header) ?? false;
            $lastNameIndex = array_search('last_name', $header) ?? array_search('lastname', $header) ?? false;
            $companyIndex = array_search('company', $header);
            $phoneIndex = array_search('phone', $header);
            $tagsIndex = array_search('tags', $header);

            $row = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $row++;
                $email = trim($data[$emailIndex] ?? '');

                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$row}: Invalid or empty email: {$email}";
                    $skipped++;
                    continue;
                }

                if (Contact::where('email', $email)->exists()) {
                    $skipped++;
                    continue;
                }

                Contact::create([
                    'email' => strtolower($email),
                    'first_name' => $firstNameIndex !== false ? trim($data[$firstNameIndex] ?? '') : null,
                    'last_name' => $lastNameIndex !== false ? trim($data[$lastNameIndex] ?? '') : null,
                    'company' => $companyIndex !== false ? trim($data[$companyIndex] ?? '') : null,
                    'phone' => $phoneIndex !== false ? trim($data[$phoneIndex] ?? '') : null,
                    'tags' => $tagsIndex !== false ? trim($data[$tagsIndex] ?? '') : null,
                    'subscribed' => true,
                    'source' => 'csv_import'
                ]);
                $imported++;
            }

            fclose($handle);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process CSV: ' . $e->getMessage());
        }

        $message = "Import complete: {$imported} contacts imported, {$skipped} skipped.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.contacts.index')->with('success', $message);
    }

    public function export()
    {
        if ($r = $this->authCheck()) return $r;

        $contacts = Contact::orderBy('email')->get();
        $filename = 'contacts_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['email', 'first_name', 'last_name', 'company', 'phone', 'tags', 'subscribed', 'created_at']);
            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->email,
                    $contact->first_name,
                    $contact->last_name,
                    $contact->company,
                    $contact->phone,
                    $contact->tags,
                    $contact->subscribed ? 'yes' : 'no',
                    $contact->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generateForm()
    {
        if ($r = $this->authCheck()) return $r;
        return view('admin.contacts.generate');
    }

    public function generate(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $validated = $request->validate([
            'start_number' => 'required|integer|min:0',
            'count' => 'required|integer|min:1|max:99999', // Max 99,999 contacts
            'digits' => 'required|integer|min:1|max:10', // e.g., 5 for 00001
            'domain' => 'required|string|regex:/^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/',
            'tags' => 'nullable|string|max:500', // Added tags field validation
        ]);

        $startNumber = $validated['start_number'];
        $count = $validated['count'];
        $digits = $validated['digits'];
        $domain = $validated['domain'];
        $tags = $validated['tags'] ?? null;

        $generationThreshold = 1000; // Define a threshold for queueing

        if ($count <= $generationThreshold) {
            // Synchronous generation for smaller batches
            $generatedCount = 0;
            $skippedCount = 0;

            for ($i = 0; $i < $count; $i++) {
                $number = str_pad($startNumber + $i, $digits, '0', STR_PAD_LEFT);
                $email = strtolower($number . '@' . $domain);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skippedCount++;
                    continue;
                }

                if (Contact::where('email', $email)->exists()) {
                    $skippedCount++;
                    continue;
                }

                Contact::create([
                    'email' => $email,
                    'tags' => $tags, // Store tags
                    'subscribed' => true,
                    'source' => 'email_generator'
                ]);
                $generatedCount++;
            }

            $message = "Generation complete: {$generatedCount} contacts added, {$skippedCount} skipped (duplicates or invalid format).";
            return redirect()->route('admin.contacts.index')->with('success', $message);

        } else {
            // Asynchronous generation for larger batches via queue
            GenerateContactsJob::dispatch($startNumber, $count, $digits, $domain, $tags);

            $message = "Generation of {$count} contacts has been queued. You will be notified when it's complete.";
            return redirect()->route('admin.contacts.index')->with('info', $message);
        }
    }
}