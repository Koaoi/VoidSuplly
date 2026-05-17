@extends('layouts.admin')
@section('title','Manajemen User')
@section('page-title','Users')

@section('content')
<form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / email..." class="input-void w-64 text-sm">
    <select name="role" class="input-void w-36 text-sm cursor-pointer">
        <option value="">Semua Role</option>
        <option value="customer" {{ request('role')==='customer'?'selected':'' }}>Customer</option>
        <option value="admin" {{ request('role')==='admin'?'selected':'' }}>Admin</option>
    </select>
    <button type="submit" class="btn-primary text-sm px-5">Filter</button>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm px-5">Reset</a>
</form>

<div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-void-border text-xs font-bold text-void-gray uppercase tracking-wider">
                <th class="text-left px-5 py-3">User</th>
                <th class="text-center px-5 py-3 hidden sm:table-cell">Role</th>
                <th class="text-center px-5 py-3 hidden md:table-cell">Orders</th>
                <th class="text-center px-5 py-3 hidden lg:table-cell">Commission</th>
                <th class="text-left px-5 py-3 hidden lg:table-cell">Bergabung</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-void-border">
            @forelse($users as $user)
                <tr class="hover:bg-void-muted/10 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full object-cover border border-void-border shrink-0">
                            <div>
                                <p class="font-semibold text-void-white text-xs">{{ $user->name }}</p>
                                <p class="text-[10px] text-void-gray truncate max-w-[160px]">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center hidden sm:table-cell">
                        @if($user->role === 'admin')
                            <span class="text-[10px] font-black bg-white text-black px-2.5 py-1 rounded-full">Admin</span>
                        @else
                            <span class="text-[10px] font-semibold text-void-gray bg-void-muted/20 border border-void-border px-2.5 py-1 rounded-full">Customer</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center hidden md:table-cell">
                        <span class="text-void-white font-bold">{{ $user->orders_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center hidden lg:table-cell">
                        <span class="text-void-white font-bold">{{ $user->commissions_count }}</span>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-void-gray text-xs">{{ $user->created_at->format('d M Y') }}</span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.show',$user) }}"
                               class="text-xs text-void-gray hover:text-void-accent transition-colors px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                                Detail
                            </a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.role',$user) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="role" value="{{ $user->role==='admin' ? 'customer' : 'admin' }}">
                                    <button type="submit"
                                            class="text-xs text-void-gray hover:text-void-accent transition-colors px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                                        {{ $user->role==='admin' ? 'Set Customer' : 'Set Admin' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-void-gray">Tidak ada user.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
        <div class="px-5 py-4 border-t border-void-border">
            {{ $users->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection