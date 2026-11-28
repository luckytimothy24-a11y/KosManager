@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">Buat Work Order</h1>
    <form method="POST" action="{{ route('tenant.work-orders.store') }}">
        @csrf
        <div><label>Kos ID</label><input type="number" name="kos_id" required></div>
        <div><label>Kamar ID</label><input type="number" name="kamar_id"></div>
        <div><label>Kategori</label><input type="text" name="category" required></div>
        <div><label>Deskripsi</label><textarea name="description" required></textarea></div>
        <div><label>Prioritas</label>
            <select name="priority">
                <option value="low">Low</option>
                <option value="normal" selected>Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        <button type="submit">Kirim</button>
    </form>
</div>
@endsection