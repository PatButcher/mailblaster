@extends('layouts.admin')
@section('title', 'Single Mail Sender')
@section('page-title', 'Single Mail Sender')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <!-- Compose Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-5"><i class="fas fa-at text-indigo-600 mr-2"></i>Compose Single Email</h3>
        <form id="single-mail-form" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">To Email <span class="text-red-500">*</span></label>
                    <input type="email" name="to_email" id="to_email" required placeholder="recipient@example.com"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">To Name</label>
                    <input type="text" name="to_name" id="to_name" placeholder="John Doe"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" id="subject" required placeholder="Email subject line"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">SMTP Provider <span class="text-red-500">*</span></label>
                <select name="smtp_provider_id" id="smtp_provider_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select SMTP Provider</option>
                    @foreach($smtpProviders as $provider)
                    <option value="{{ $provider->id }}"
                        data-host="{{ $provider->host }}"
                        data-port="{{ $provider->port }}"
                        data-enc="{{ strtoupper($provider->encryption) }}"
                        data-from="{{ $provider->from_email }}"
                        data-limit="{{ $provider->max_daily_emails }}"
                        data-used="{{ $provider->daily_sent_count }}">
                        {{ $provider->name }} — {{ $provider->host }} ({{ $provider->remaining_today }} remaining today)
                    </option>
                    @endforeach
                </select>
                <div id="smtp-info" class="hidden mt-2 p-3 bg-indigo-50 border border-indigo-200 rounded-lg text-xs text-indigo-700 space-y-1"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">HTML Body <span class="text-red-500">*</span></label>
                <textarea name="body_html" id="body_html" rows="8" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="<h1>Hello!</h1><p>Your message here...</p>"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Plain Text Version</label>
                <textarea name="body_text" id="body_text" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Plain text fallback"></textarea>
            </div>
            <button type="submit" id="send-btn"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg text-sm transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-paper-plane"></i>
                <span>Send Email & Monitor SMTP</span>
            </button>
        </form>
    </div>

    <!-- Monitor Panel -->
    <div class="space-y-4">
        <!-- SMTP Real-time Monitor -->
        <div class="bg-gray-900 rounded-xl shadow-sm border border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <div class="flex space-x-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span class="text-gray-400 text-sm font-mono ml-2">SMTP Debug Console</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span id="smtp-status-indicator" class="w-2 h-2 rounded-full bg-gray-600"></span>
                    <span id="smtp-status-text" class="text-gray-500 text-xs">Idle</span>
                    <button onclick="clearSmtpLog()" class="text-gray-500 hover:text-gray-300 text-xs"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div id="smtp-log" class="font-mono text-xs p-4 h-72 overflow-y-auto space-y-0.5 text-gray-400">
                <div class="text-gray-600">» Waiting for send command...</div>
            </div>
        </div>

        <!-- Laravel Log Monitor -->
        <div class="bg-gray-800 rounded-xl shadow-sm border border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-file-alt text-gray-400 text-sm"></i>
                    <span class="text-gray-400 text-sm font-mono">Laravel Log Monitor</span>
                </div>
                <div class="flex items-center space-x-3">
                    <select id="log-lines" class="bg-gray-700 border border-gray-600 text-gray-300 text-xs rounded px-2 py-1">
                        <option value="50">Last 50 lines</option>
                        <option value="100" selected>Last 100 lines</option>
                        <option value="200">Last 200 lines</option>
                    </select>
                    <button onclick="loadLaravelLog()" class="text-gray-400 hover:text-gray-200 text-xs bg-gray-700 px-3 py-1 rounded"><i class="fas fa-sync mr-1"></i>Refresh</button>
                    <button onclick="clearLaravelLog()" class="text-gray-500 hover:text-gray-300 text-xs"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div id="laravel-log" class="font-mono text-xs p-4 h-64 overflow-y-auto space-y-0.5 text-gray-400">
                <div class="text-gray-600">» Click Refresh to load latest Laravel log entries...</div>
            </div>
        </div>
    </div>
</div>

<script>
const smtpLog      = document.getElementById('smtp-log');
const laravelLog   = document.getElementById('laravel-log');
const statusInd    = document.getElementById('smtp-status-indicator');
const statusTxt    = document.getElementById('smtp-status-text');
const sendBtn      = document.getElementById('send-btn');
const smtpInfo     = document.getElementById('smtp-info');

function appendToLog(container, text, type) {
    const line = document.createElement('div');
    const colors = {
        log:     'text-gray-300',
        success: 'text-green-400 font-semibold',
        error:   'text-red-400 font-semibold',
        warning: 'text-yellow-400',
        info:    'text-blue-400',
        done:    ''
    };
    if (type === 'done') return;
    line.className = colors[type] || 'text-gray-300';
    line.textContent = text;
    container.appendChild(line);
    container.scrollTop = container.scrollHeight;
}

function clearSmtpLog()  { smtpLog.innerHTML  = '<div class="text-gray-600">» Log cleared.</div>'; }
function clearLaravelLog() { laravelLog.innerHTML = '<div class="text-gray-600">» Log cleared.</div>'; }

function setStatus(status) {
    const states = {
        idle:       ['bg-gray-600', 'Idle'],
        connecting: ['bg-yellow-500', 'Connecting...'],
        sending:    ['bg-blue-500 animate-pulse', 'Sending...'],
        success:    ['bg-green-500', 'Sent Successfully'],
        error:      ['bg-red-500', 'Failed'],
    };
    const [cls, txt] = states[status] || states.idle;
    statusInd.className = `w-2 h-2 rounded-full ${cls}`;
    statusTxt.textContent = txt;
}

// Show SMTP provider info on select
document.getElementById('smtp_provider_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) { smtpInfo.classList.add('hidden'); return; }
    smtpInfo.classList.remove('hidden');
    const used = parseInt(opt.dataset.used), limit = parseInt(opt.dataset.limit);
    const pct  = Math.round((used / limit) * 100);
    smtpInfo.innerHTML = `
        <p><strong>Host:</strong> ${opt.dataset.host}:${opt.dataset.port} [${opt.dataset.enc}]</p>
        <p><strong>From:</strong> ${opt.dataset.from}</p>
        <p><strong>Daily usage:</strong> ${used} / ${limit} (${pct}% used)</p>`;
});

// Form submit → create token → stream
document.getElementById('single-mail-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i><span>Preparing...</span>';
    clearSmtpLog();
    setStatus('connecting');

    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name=csrf-token]').content;

    try {
        // Step 1: Create send token
        const res   = await fetch('{{ route("admin.single-mail.send") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        });
        const json  = await res.json();
        if (!json.token) throw new Error(json.message || 'Failed to create send token');

        sendBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i><span>Sending...</span>';
        setStatus('sending');

        // Step 2: Open SSE stream
        const es = new EventSource(`/admin/single-mail/stream/${json.token}`);

        es.addEventListener('log', (e) => {
            const d = JSON.parse(e.data);
            appendToLog(smtpLog, d.line, 'log');
        });
        es.addEventListener('success', (e) => {
            const d = JSON.parse(e.data);
            appendToLog(smtpLog, d.line, 'success');
            setStatus('success');
        });
        es.addEventListener('error', (e) => {
            if (e.data) {
                const d = JSON.parse(e.data);
                appendToLog(smtpLog, d.line, 'error');
            }
            setStatus('error');
        });
        es.addEventListener('done', () => {
            es.close();
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Send Email & Monitor SMTP</span>';
        });

        es.onerror = () => {
            es.close();
            setStatus('error');
            appendToLog(smtpLog, '✗ Connection to stream lost', 'error');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Send Email & Monitor SMTP</span>';
        };

    } catch(err) {
        appendToLog(smtpLog, '✗ ' + err.message, 'error');
        setStatus('error');
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Send Email & Monitor SMTP</span>';
    }
});

async function loadLaravelLog() {
    const lines = document.getElementById('log-lines').value;
    laravelLog.innerHTML = '<div class="text-yellow-400">» Loading...</div>';
    const es = new EventSource(`/admin/single-mail/laravel-log?lines=${lines}`);
    laravelLog.innerHTML = '';
    es.addEventListener('log',     (e) => appendToLog(laravelLog, JSON.parse(e.data).line, 'log'));
    es.addEventListener('error',   (e) => { if(e.data) appendToLog(laravelLog, JSON.parse(e.data).line, 'error'); });
    es.addEventListener('warning', (e) => appendToLog(laravelLog, JSON.parse(e.data).line, 'warning'));
    es.addEventListener('info',    (e) => appendToLog(laravelLog, JSON.parse(e.data).line, 'info'));
    es.addEventListener('done',    ()  => { es.close(); laravelLog.scrollTop = laravelLog.scrollHeight; });
    es.onerror = () => es.close();
}
</script>
@endsection
