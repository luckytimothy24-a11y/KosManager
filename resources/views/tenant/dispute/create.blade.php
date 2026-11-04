@extends('layouts.app')

@section('title', 'Buat Dispute')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Buat Dispute Baru</h1>

    <form method="POST" action="{{ route('tenant.dispute.store') }}" class="card-surface p-6 space-y-4">
        @csrf

        <div>
            <label class="field-label">Tipe Dispute</label>
            <select name="type" class="field-input" required>
                <option value="payment">Pembayaran</option>
                <option value="billing">Tagihan</option>
                <option value="service">Layanan</option>
                <option value="other">Lainnya</option>
            </select>
        </div>

        <div>
            <label class="field-label">Judul</label>
            <input type="text" name="title" class="field-input" required maxlength="255" placeholder="Ringkasan masalah...">
        </div>

        <div>
            <label class="field-label">Deskripsi</label>
            <textarea name="description" class="field-input" rows="4" required maxlength="2000" placeholder="Jelaskan masalah Anda secara detail..."></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('tenant.dispute.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Kirim Dispute</button>
        </div>
    </form>
</div>
@endsection