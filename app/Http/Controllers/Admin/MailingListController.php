<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailingList;
use Illuminate\Http\Request;

class MailingListController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index()
    {
        if ($r = $this->authCheck()) return $r;
        $mailingLists = MailingList::withCount('contacts')->orderBy('name')->paginate(10);
        return view('admin.mailing_lists.index', compact('mailingLists'));
    }

    public function create()
    {
        if ($r = $this->authCheck()) return $r;
        return view('admin.mailing_lists.create');
    }

    public function store(Request $request)
    {
        if ($r = $this->authCheck()) return $r;
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:mailing_lists',
            'description' => 'nullable|string|max:1000',
        ]);
        MailingList::create($validated);
        return redirect()->route('admin.mailing_lists.index')->with('success', 'Mailing list created successfully.');
    }

    public function edit(MailingList $mailingList)
    {
        if ($r = $this->authCheck()) return $r;
        return view('admin.mailing_lists.edit', compact('mailingList'));
    }

    public function update(Request $request, MailingList $mailingList)
    {
        if ($r = $this->authCheck()) return $r;
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:mailing_lists,name,' . $mailingList->id,
            'description' => 'nullable|string|max:1000',
        ]);
        $mailingList->update($validated);
        return redirect()->route('admin.mailing_lists.index')->with('success', 'Mailing list updated successfully.');
    }

    public function destroy(MailingList $mailingList)
    {
        if ($r = $this->authCheck()) return $r;
        $mailingList->delete();
        return back()->with('success', 'Mailing list deleted successfully.');
    }
}
