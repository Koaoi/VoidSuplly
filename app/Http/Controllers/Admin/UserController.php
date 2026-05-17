<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount(['orders','commissions','reviews'])->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($qb) => $qb->where('name','like',"%$q%")
                                        ->orWhere('email','like',"%$q%"));
        }
        if ($request->filled('role')) $query->where('role',$request->role);

        $users = $query->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['orders'=>fn($q)=>$q->latest()->take(5),'commissions'=>fn($q)=>$q->latest()->take(5)]);
        return view('admin.users.show', compact('user'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role'=>['required','in:admin,customer']]);

        if ($user->id === auth()->id()) {
            return back()->with('error','Tidak bisa mengubah role akun sendiri.');
        }

        $user->update(['role' => $request->role]);
        return back()->with('success',"Role {$user->name} diubah menjadi {$request->role}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error','Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success',"User {$user->name} berhasil dihapus.");
    }
}