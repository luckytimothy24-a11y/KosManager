@extends('layouts.app')

@section('title', 'Dispute Saya')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dispute Saya</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar komplain yang Anda ajukan.</p>
        </div>
        <a href="{{ route('tenant.dispute.create') }}" class="btn-primary">
            <i class="ri-add-line"></i> Buat Dispute
        </a>
    </div>

    @if($disputes->isEmpty())
        <div class="card-surface p-8 text-center">
            <i class="ri-mail-line text-4xl text-slate-300 dark:text-slate-600"></i>
            <p class="mt-3 text-slate-500 dark:text-slate-400">Belum ada dispute.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($disputes as $dispute)
                <a href="{{ route('tenant.dispute.show', $dispute) }}" class="block card-surface p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $dispute->title }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $dispute->type }} · {{ $dispute->created_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                            @if($dispute->status === 'open') bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300
                            @elseif($dispute->status === 'in_progress') bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-300
                            @elseif($dispute->status === 'resolved') bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300
                            @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $disputes->links() }}</div>
    @endif
</div>
@endsection