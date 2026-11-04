@extends('layouts.app')

@section('title', 'Detail Dispute')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <a href="{{ route('owner.dispute.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline mb-4 inline-block">
        <i class="ri-arrow-left-line"></i> Kembali
    </a>

    <div class="card-surface p-6 mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $dispute->title }}</h1>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                @if($dispute->status === 'open') bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300
                @elseif($dispute->status === 'in_progress') bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-300
                @elseif($dispute->status === 'resolved') bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300
                @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300
                @endif">
                {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
            </span>
        </div>

        <div class="space-y-3 text-sm mb-4">
            <div><span class="text-slate-500 dark:text-slate-400">Tenant:</span> <span class="font-medium text-slate-900 dark:text-white ml-2">{{ $dispute->user->name ?? 'N/A' }}</span></div>
            <div><span class="text-slate-500 dark:text-slate-400">Tipe:</span> <span class="font-medium text-slate-900 dark:text-white ml-2">{{ ucfirst($dispute->type) }}</span></div>
            <div><span class="text-slate-500 dark:text-slate-400">Tanggal:</span> <span class="font-medium text-slate-900 dark:text-white ml-2">{{ $dispute->created_at->format('d/m/Y H:i') }}</span></div>
            <div><span class="text-slate-500 dark:text-slate-400">Deskripsi:</span><p class="mt-1 text-slate-900 dark:text-white">{{ $dispute->description }}</p></div>
        </div>

        <form method="POST" action="{{ route('owner.dispute.resolve', $dispute) }}" class="border-t border-slate-100 dark:border-white/[0.08] pt-4 space-y-3">
            @csrf
            <div>
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                    <option value="in_progress" {{ $dispute->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ $dispute->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $dispute->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div>
                <label class="field-label">Resolusi</label>
                <textarea name="resolution" class="field-input" rows="3" placeholder="Jelaskan resolusi...">{{ $dispute->resolution }}</textarea>
            </div>
            <button type="submit" class="btn-primary">Update Status</button>
        </form>
    </div>
</div>
@endsection