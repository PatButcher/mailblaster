<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
// use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
// use Symfony\Component\Mime\Email;

class SmtpProviderController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->authCheck()) return $r;

        $providers = SmtpProvider::orderBy('priority')->orderBy('name')->paginate(15);
        return view('admin.smtp.index', compact('providers'));
    }

    public function create()
    {
        if ($r = $this->authCheck()) return $r;
        return view('admin.smtp.create');
    }

    public function store(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|in:25,465,587,2525',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'max_daily_emails' => 'required|integer|min:1|max:100000',
            'priority' => 'required|integer|min:1|max:100',
            'active' => 'boolean'
        ]);

        $validated['active'] = $request->has('active');
        SmtpProvider::create($validated);

        return redirect()->route('admin.smtp.index')->with('success', 'SMTP provider added successfully.');
    }

    public function edit($id)
    {
        if ($r = $this->authCheck()) return $r;
        $provider = SmtpProvider::findOrFail($id);
        return view('admin.smtp.edit', compact('provider'));
    }

    public function update(Request $request, $id)
    {
        if ($r = $this->authCheck()) return $r;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|in:25,465,587,2525',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'max_daily_emails' => 'required|integer|min:1|max:100000',
            'priority' => 'required|integer|min:1|max:100',
            'active' => 'boolean'
        ]);

        $provider = SmtpProvider::findOrFail($id);
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        $validated['active'] = $request->has('active');
        $provider->update($validated);

        return redirect()->route('admin.smtp.index')->with('success', 'SMTP provider updated successfully.');
    }

    public function destroy($id)
    {
        if ($r = $this->authCheck()) return $r;
        SmtpProvider::findOrFail($id)->delete();
        return redirect()->route('admin.smtp.index')->with('success', 'SMTP provider deleted.');
    }

    // /**
    //  * Build the message. NEVER USED - PART OF FLEETCART EMAIL SYSTEM (IDEA)
    //  *
    //  * @return $this
    //  */
    // private function build()
    // {
    //     return $this->subject(trans('user::mail.welcome', ['name' => $this->firstName]))
    //         ->view("storefront::emails.{$this->getViewName()}", [
    //             'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
    //         ]);
    // }

    public function test(Request $request, $id)
    {
        if ($r = $this->authCheck()) return $r;
        $provider = SmtpProvider::findOrFail($id);
        $request->validate(['test_email' => 'required|email']);

        try {
            // $transport = new \Swift_SmtpTransport($provider->host, $provider->port);
            // if ($provider->encryption !== 'none') {
            //     $transport->setEncryption($provider->encryption);
            // }
            // $transport->setUsername($provider->username);
            // $transport->setPassword($provider->password);

            config([
                // 'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $provider->host,
                'mail.mailers.smtp.port' => $provider->port,
                'mail.mailers.smtp.encryption' => $provider->encryption === 'none' ? null : $provider->encryption,
                'mail.mailers.smtp.username' => $provider->username,
                'mail.mailers.smtp.password' => $provider->password,
                'mail.from.address' => $provider->from_email,
                'mail.from.name' => $provider->from_name,
            ]);

            // dd(config());

            Mail::raw('This is a test email from your Mass Email System using provider: ' . $provider->name, function ($message) use ($request, $provider) {
                $message->to($request->test_email)
                    ->subject('SMTP Test - ' . $provider->name)
                    ->from($provider->from_email, $provider->from_name);
            });

            $smtp->increment('daily_sent_count');
            $smtp->increment('total_sent_count');

            $provider->update(['last_tested_at' => now(), 'test_status' => 'success']);
            return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            $provider->update(['last_tested_at' => now(), 'test_status' => 'failed']);
            return back()->with('error', 'SMTP test failed: ' . $e->getMessage());
        }
    }

    public function resetDailyCount($id)
    {
        if ($r = $this->authCheck()) return $r;
        $provider = SmtpProvider::findOrFail($id);
        $provider->update(['daily_sent_count' => 0, 'daily_reset_at' => now()]);
        return back()->with('success', 'Daily count reset for ' . $provider->name);
    }
}