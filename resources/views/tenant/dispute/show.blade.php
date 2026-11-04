@extends('layouts.app')

@section('title', 'Detail Dispute')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <a href="{{ route('tenant.dispute.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline mb-4 inline-block">
        <i class="ri-arrow-left-line"></i> Kembali
    </a>

    <div class="card-surface p-6">
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

        <div class="space-y-3 text-sm">
            <div>
                <span class="text-slate-500 dark:text-slate-400">Tipe:</span>
                <span class="font-medium text-slate-900 dark:text-white ml-2">{{ ucfirst($dispute->type) }}</span>
            </div>
            <div>
                <span class="text-slate-500 dark:text-slate-400">Tanggal:</span>
                <span class="font-medium text-slate-900 dark:text-white ml-2">{{ $dispute->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="text-slate-500 dark:text-slate-400">Deskripsi:</span>
                <p class="mt-1 text-slate-900 dark:text-white">{{ $dispute->description }}</p>
            </div>
            @if($dispute->resolution)
                <div class="mt-4 p-3 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20">
                    <span class="text-green-700 dark:text-green-300 font-semibold">Resolusi:</span>
                    <p class="mt-1 text-green-800 dark:text-green-200">{{ $dispute->resolution }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection