@extends('layouts.admin')
@section('title', 'Campaigns - MailBlast')
@section('page-title', 'Email Campaigns')

@section('content')
<div class="flex items-center justify-between mb-6">
    <form method="GET" class="flex items-center space-x-2">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['draft','queued','sending','completed','paused','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('admin.campaigns.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i class="fas fa-plus mr-2"></i>New Campaign
    </a>
</div>

<div class="space-y-4">
    @forelse($campaigns as $campaign)
    @php
        $colors = ['draft'=>'gray','queued'=>'blue','sending'=>'yellow','completed'=>'green','paused'=>'orange','cancelled'=>'red'];
        $color = $colors[$campaign->status] ?? 'gray';
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $campaign->name }}</h3>
                    @if($campaign->is_recurring)
                        <span class="text-indigo-600" title="Recurring Campaign"><i class="fas fa-redo"></i></span>
                    @endif
                    <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $campaign->status }}</span>
                </div>
                <p class="text-sm text-gray-500 mb-3">Subject: {{ $campaign->subject }}</p>
                <div class="flex items-center space-x-6 text-sm">
                    <span class="text-gray-500"><i class="fas fa-users mr-1"></i>{{ number_format($campaign->total_recipients) }} recipients</span>
                    <span class="text-green-600"><i class="fas fa-check mr-1"></i>{{ number_format($campaign->sent_count ?? 0) }} sent</span>
                    <span class="text-red-500"><i class="fas fa-times mr-1"></i>{{ number_format($campaign->failed_count ?? 0) }} failed</span>
                    <span class="text-gray-400"><i class="fas fa-clock mr-1"></i>{{ $campaign->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="flex items-center space-x-2 ml-4">
                @if(in_array($campaign->status, ['draft', 'paused']))
                <form action="@if($campaign->status === 'paused'){{ route('admin.campaigns.resume',$campaign->id) }}@else{{ route('admin.campaigns.send',$campaign->id) }}@endif" method="POST" class="inline">
                    @csrf
                    @if ($campaign->status == 'paused')
                    <button type="submit" onclick="return confirm('Resume this campaign and queue for sending?')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                        <i class="fas fa-paper-plane mr-1"></i>{{ $campaign->status === 'paused' ? 'Resume' : 'Send' }}
                    </button>
                    @else
                    <button type="submit" onclick="return confirm('Queue this campaign for sending?')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                        <i class="fas fa-paper-plane mr-1"></i>{{ $campaign->status === 'paused' ? 'Resume' : 'Send' }}
                    </button>
                    @endif
                </form>
                @endif
                @if(in_array($campaign->status, ['queued', 'sending']))
                <form action="{{ route('admin.campaigns.pause', $campaign->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                        <i class="fas fa-pause mr-1"></i>Pause
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="border border-indigo-300 text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-medium">
                    <i class="fas fa-eye mr-1"></i>View
                </a>
                @if(in_array($campaign->status, ['draft', 'paused']))
                <a href="{{ route('admin.campaigns.edit', $campaign->id) }}" class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg text-xs font-medium">
                    <i class="fas fa-edit mr-1"></i>Edit
                </a>
                @endif
                @if($campaign->status !== 'sending')
                <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this campaign and all its logs?')" class="border border-red-300 text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @if(in_array($campaign->status, ['sending', 'queued', 'completed']) && $campaign->total_recipients > 0)
        @php $progress = $campaign->progress_percent; @endphp
        <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Progress</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <i class="fas fa-bullhorn text-5xl text-gray-300 mb-4 block"></i>
        <h3 class="text-lg font-medium text-gray-600 mb-1">No campaigns yet</h3>
        <p class="text-gray-400 text-sm mb-4">Create your first email campaign to get started</p>
        <a href="{{ route('admin.campaigns.create') }}" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm hover:bg-indigo-700">Create Campaign</a>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $campaigns->links() }}</div>
@endsection
