@extends('layouts.admin')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $unreadCount }} unread notification{{ $unreadCount !== 1 ? 's' : '' }}</p>
    @if($unreadCount > 0)
    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
        @csrf
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            <i class="fas fa-check-double mr-2"></i>Mark All as Read
        </button>
    </form>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
    @forelse($notifications as $n)
    @php
        $colorMap = ['blue'=>'bg-blue-100 text-blue-600','green'=>'bg-green-100 text-green-600','red'=>'bg-red-100 text-red-600','orange'=>'bg-orange-100 text-orange-600','indigo'=>'bg-indigo-100 text-indigo-600','gray'=>'bg-gray-100 text-gray-500'];
        $colorClass = $colorMap[$n->color] ?? $colorMap['blue'];
    @endphp
    <div class="flex items-start space-x-4 p-5 {{ !$n->read ? 'bg-indigo-50/40' : '' }} hover:bg-gray-50">
        <div class="w-10 h-10 rounded-xl {{ $colorClass }} flex items-center justify-center flex-shrink-0">
            <i class="fas fa-{{ $n->icon }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800 {{ !$n->read ? '' : 'font-normal' }}">{{ $n->title }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $n->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->format('M d, Y H:i:s') }} &bull; {{ $n->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center space-x-3 ml-4 flex-shrink-0">
                    @if(!$n->read)
                    <button onclick="fetch('/admin/notifications/{{ $n->id }}/read', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>this.closest('div.flex').classList.remove('bg-indigo-50/40'));this.remove()" class="text-indigo-600 hover:text-indigo-800 text-xs"><i class="fas fa-check"></i> Mark read</button>
                    @endif
                    @if($n->link)
                    <a href="{{ $n->link }}" class="text-indigo-600 hover:underline text-xs">View →</a>
                    @endif
                    <form action="{{ route('admin.notifications.destroy', $n->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-500 text-xs"><i class="fas fa-times"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="px-6 py-16 text-center">
        <i class="fas fa-bell text-5xl text-gray-200 mb-4 block"></i>
        <p class="text-gray-400">No notifications yet.</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
