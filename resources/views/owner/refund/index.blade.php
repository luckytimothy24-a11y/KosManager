@extends('layouts.app')

@section('title', 'Refund Pending')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Refund Pending</h1>

    @if($refunds->isEmpty())
        <div class="card-surface p-8 text-center">
            <i class="ri-mail-line text-4xl text-slate-300 dark:text-slate-600"></i>
            <p class="mt-3 text-slate-500 dark:text-slate-400">Tidak ada refund pending.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($refunds as $refund)
                <div class="card-surface p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $refund->payment_number }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ $refund->penghuni?->user?->name ?? $refund->booking?->user?->name ?? 'N/A' }} ·
                                Rp {{ number_format($refund->refund_amount, 0, ',', '.') }} ·
                                {{ $refund->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('owner.refund.approve', $refund) }}">
                                @csrf
                                <button type="submit" class="btn-primary text-xs">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('owner.refund.reject', $refund) }}">
                                @csrf
                                <input type="hidden" name="reason" value="Ditolak oleh owner">
                                <button type="submit" class="btn-secondary text-xs">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $refunds->links() }}</div>
    @endif
</div>
@endsection