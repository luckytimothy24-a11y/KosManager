@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">Work Order #{{ $workOrder->id }}</h1>
    <p><strong>Kategori:</strong> {{ $workOrder->category }}</p>
    <p><strong>Status:</strong> {{ $workOrder->status }}</p>
    <p><strong>Prioritas:</strong> {{ $workOrder->priority }}</p>
    <p><strong>Deskripsi:</strong> {{ $workOrder->description }}</p>
    @if($workOrder->resolution)<p><strong>Resolusi:</strong> {{ $workOrder->resolution }}</p>@endif
    @if(auth()->user()->isTechnician() || auth()->user()->isAdmin() || auth()->user()->isOwner() || auth()->user()->isSuperAdmin())
        <form method="POST" action="{{ route(auth()->user()->isAdmin() ? 'admin.work-orders.update' : (auth()->user()->isTechnician() ? 'technician.work-orders.update' : 'owner.work-orders.update'), $workOrder) }}">
            @csrf @method('PUT')
            <select name="status">
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>
            <textarea name="resolution" placeholder="Resolusi"></textarea>
            <button type="submit">Update</button>
        </form>
    @endif
</div>
@endsection