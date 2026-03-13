<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MailBlast')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .sidebar-link.active { background-color: rgba(255,255,255,0.15); }
        .sidebar-link:hover  { background-color: rgba(255,255,255,0.1); }
        #notif-panel { transition: transform 0.3s ease, opacity 0.3s ease; }
        .notif-item { animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .toast { animation: slideIn 0.3s ease; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2 w-80"></div>

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div class="w-64 bg-gradient-to-b from-indigo-800 to-indigo-900 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-indigo-700">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">MailBlast</h1>
                    <p class="text-indigo-300 text-xs">Mass Email System</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <p class="text-indigo-400 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Overview</p>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie w-5"></i><span>Dashboard</span>
            </a>
            <p class="text-indigo-400 text-xs font-semibold uppercase tracking-wider px-3 mt-4 mb-2">Email</p>
            <a href="{{ route('admin.campaigns.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
                <i class="fas fa-paper-plane w-5"></i><span>Campaigns</span>
            </a>
            <a href="{{ route('admin.single-mail.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.single-mail.*') ? 'active' : '' }}">
                <i class="fas fa-at w-5"></i><span>Single Mail Sender</span>
            </a>
            <a href="{{ route('admin.queue.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}">
                <i class="fas fa-stream w-5"></i><span>Send Queue</span>
                @php $queueCount = \App\Models\EmailLog::where('status', 'queued')->count(); @endphp
                @if($queueCount > 0)
                <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full px-2 py-0.5">{{ $queueCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.logs.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5"></i><span>Email Logs</span>
            </a>
            <p class="text-indigo-400 text-xs font-semibold uppercase tracking-wider px-3 mt-4 mb-2">Management</p>
            <a href="{{ route('admin.contacts.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                <i class="fas fa-users w-5"></i><span>Contacts</span>
            </a>
            <a href="{{ route('admin.mailing_lists.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.mailing_lists.*') ? 'active' : '' }}">
                <i class="fas fa-list w-5"></i><span>Mailing Lists</span>
            </a>
            <a href="{{ route('admin.smtp.index') }}" class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.smtp.*') ? 'active' : '' }}">
                <i class="fas fa-server w-5"></i><span>SMTP Providers</span>
            </a>
        </nav>
        <div class="p-4 border-t border-indigo-700">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold">{{ strtoupper(substr(session('admin_user', 'A'), 0, 1)) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ session('admin_user', 'Admin') }}</p>
                    <p class="text-xs text-indigo-300">{{ session('admin_email', '') }}</p>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left text-sm text-indigo-300 hover:text-white flex items-center space-x-2 px-2 py-1 rounded hover:bg-indigo-700">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top bar -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
            <div class="flex items-center space-x-4">
                <!-- Notification Bell -->
                <div class="relative">
                    <button id="notif-btn" class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                    </button>
                    <!-- Notification Panel -->
                    <div id="notif-panel" class="hidden absolute right-0 top-12 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 max-h-96 overflow-hidden flex flex-col">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800">Notifications</h3>
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:underline">Mark all read</button>
                                </form>
                                <a href="{{ route('admin.notifications.index') }}" class="text-xs text-gray-500 hover:underline ml-2">View all</a>
                            </div>
                        </div>
                        <div id="notif-list" class="overflow-y-auto flex-1">
                            <div class="px-4 py-8 text-center text-gray-400 text-sm" id="notif-empty">No notifications</div>
                        </div>
                    </div>
                </div>
                <span class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</span>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-6 pt-4">
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center mb-4">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>{{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center mb-4">
                <i class="fas fa-times-circle mr-3 text-red-500"></i>{{ session('error') }}
            </div>
            @endif
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif
        </div>

        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
// ─── Notification System ─────────────────────────────────────────────────────
const notifBtn   = document.getElementById('notif-btn');
const notifPanel = document.getElementById('notif-panel');
const notifBadge = document.getElementById('notif-badge');
const notifList  = document.getElementById('notif-list');
const notifEmpty = document.getElementById('notif-empty');
const toastCont  = document.getElementById('toast-container');

let lastId = 0;
let panelOpen = false;

const colorMap = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    red: 'bg-red-100 text-red-600',
    orange: 'bg-orange-100 text-orange-600',
    indigo: 'bg-indigo-100 text-indigo-600',
    gray: 'bg-gray-100 text-gray-500',
};

notifBtn.addEventListener('click', () => {
    panelOpen = !panelOpen;
    notifPanel.classList.toggle('hidden', !panelOpen);
    if (panelOpen) markVisible();
});

document.addEventListener('click', (e) => {
    if (!notifBtn.contains(e.target) && !notifPanel.contains(e.target)) {
        panelOpen = false;
        notifPanel.classList.add('hidden');
    }
});

function markVisible() {
    const ids = [...notifList.querySelectorAll('[data-notif-id]')].map(el => el.dataset.notifId);
    ids.forEach(id => {
        fetch(`/admin/notifications/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
    });
    notifBadge.classList.add('hidden');
    notifBadge.textContent = '0';
}

function renderNotif(n) {
    const colors = colorMap[n.color] || colorMap.blue;
    return `<div data-notif-id="${n.id}" class="notif-item flex items-start space-x-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 ${n.read ? 'opacity-60' : ''}">
        <div class="w-8 h-8 rounded-lg ${colors} flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas fa-${n.icon} text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800">${n.title}</p>
            <p class="text-xs text-gray-500 truncate">${n.message}</p>
            <p class="text-xs text-gray-400 mt-0.5">${n.time}</p>
        </div>
        ${n.link ? `<a href="${n.link}" class="text-indigo-600 text-xs hover:underline flex-shrink-0">View</a>` : ''}
    </div>`;
}

function showToast(n) {
    const colors = colorMap[n.color] || colorMap.blue;
    const toast = document.createElement('div');
    toast.className = 'toast bg-white border border-gray-200 rounded-xl shadow-lg p-4 flex items-start space-x-3';
    toast.innerHTML = `
        <div class="w-8 h-8 rounded-lg ${colors} flex items-center justify-center flex-shrink-0">
            <i class="fas fa-${n.icon} text-sm"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-800">${n.title}</p>
            <p class="text-xs text-gray-500">${n.message}</p>
        </div>
        <button onclick="this.closest('.toast').remove()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xs"></i></button>`;
    toastCont.appendChild(toast);
    setTimeout(() => toast.remove(), 6000);
}

async function pollNotifications() {
    try {
        const res  = await fetch(`/admin/notifications/feed?last_id=${lastId}`);
        const data = await res.json();
        // SSE returns wrapped; for polling we just fetch the feed directly
    } catch(e) {}
}

function startNotificationPolling() {
    async function poll() {
        try {
            // Use a simple JSON endpoint instead of SSE for polling
            const res = await fetch(`/admin/notifications/feed?last_id=${lastId}&_=${Date.now()}`);
            const text = await res.text();
            const match = text.match(/data: (.+)/);
            if (match) {
                const data = JSON.parse(match[1]);
                const newNotifs = data.notifications || [];
                if (newNotifs.length > 0) {
                    lastId = Math.max(lastId, data.last_id); // Ensure lastId always increases
                    const unread = data.unread_count || 0;
                    if (unread > 0) {
                        notifBadge.textContent = unread > 99 ? '99+' : unread;
                        notifBadge.classList.remove('hidden');
                    } else {
                        notifBadge.classList.add('hidden');
                    }
                    newNotifs.forEach(n => {
                        if (!n.read) showToast(n);
                        const existing = notifList.querySelector(`[data-notif-id="${n.id}"]`);
                        if (!existing) {
                            notifEmpty.style.display = 'none';
                            notifList.insertAdjacentHTML('afterbegin', renderNotif(n));
                        }
                    });
                }
            }
        } catch(e) {}
        setTimeout(poll, 8000);
    }
    setTimeout(poll, 2000);
}

startNotificationPolling();
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@stack('scripts')
</body>
</html>
