@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">Work Orders</h1>
    @if(session('success'))<div class="mb-4 text-green-600">{{ session('success') }}</div>@endif
    @if(isset($orders))
        <ul>
        @foreach($orders as $order)
            <li><a href="{{ route('owner.work-orders.show', $order) }}">{{ $order->category }} - {{ $order->status }}</a></li>
        @endforeach
        </ul>
        {{ $orders->links() ?? '' }}
    @endif
    @if(auth()->user()->isPencari())
        <a href="{{ route('tenant.work-orders.create') }}" class="btn">Buat Work Order</a>
    @endif
</div>
@endsection