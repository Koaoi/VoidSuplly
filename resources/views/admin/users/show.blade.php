@extends('layouts.admin')
@section('title','Detail User — ' . $user->name)
@section('page-title','User — ' . $user->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-void-card border border-void-border rounded-2xl p-6">
        <div class="flex flex-col items-center text-center mb-5">
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full object-cover border-2 border-void-border mb-3">
            <h2 class="text-lg font-black text-void-white">{{ $user->name }}</h2>
            <p class="text-xs text-void-gray">{{ $user->email }}</p>
            @if($user->role==='admin')
                <span class="mt-2 text-[10px] font-black bg-white text-black px-2.5 py-0.5 rounded-full">Administrator</span>
            @endif
        </div>
        <div class="space-y-2 text-xs border-t border-void-border pt-4">
            <div class="flex justify-between"><span class="text-void-gray">Bergabung</span><span class="text-void-light">{{ $user->created_at->format('d M Y') }}</span></div>
            <div class="flex justify-between"><span class="text-void-gray">Login via</span><span class="text-void-light">{{ $user->google_id ? 'Google' : 'Email' }}</span></div>
            <div class="flex justify-between"><span class="text-void-gray">Total Order</span><span class="text-void-light font-bold">{{ $user->orders_count }}</span></div>
        </div>
        @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.role',$user) }}" class="mt-4">
                @csrf @method('PATCH')
                <input type="hidden" name="role" value="{{ $user->role==='admin' ? 'customer' : 'admin' }}">
                <button type="submit" class="w-full btn-secondary text-sm py-2.5">
                    Ubah Role → {{ $user->role==='admin' ? 'Customer' : 'Admin' }}
                </button>
            </form>
        @endif
    </div>

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-void-border">
                <h3 class="text-xs font-bold tracking-widest text-void-white uppercase">Order Terbaru</h3>
            </div>
            <div class="divide-y divide-void-border">
                @forelse($user->orders as $order)
                    <a href="{{ route('admin.orders.show',$order) }}"
                       class="flex items-center justify-between px-5 py-3 hover:bg-void-muted/20 transition-colors">
                        <div>
                            <p class="text-xs font-bold text-void-accent">{{ $order->order_code }}</p>
                            <p class="text-[10px] text-void-gray">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-void-white">{{ $order->formatted_total }}</p>
                            <p class="text-[10px] text-void-gray capitalize">{{ $order->status_label }}</p>
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-6 text-xs text-void-gray text-center">Belum ada order.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection