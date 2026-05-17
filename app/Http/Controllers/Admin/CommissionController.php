<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Commission::with('user')->latest();

        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($qb) => $qb->where('title','like',"%$q%")
                                        ->orWhereHas('user', fn($u) => $u->where('name','like',"%$q%")));
        }

        $commissions  = $query->paginate(15)->withQueryString();
        $statusCounts = Commission::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total','status')->toArray();

        return view('admin.commissions.index', compact('commissions','statusCounts'));
    }

    public function show(Commission $commission)
    {
        $commission->load('user');
        return view('admin.commissions.show', compact('commission'));
    }

    public function updateStatus(Request $request, Commission $commission)
    {
        $request->validate([
            'status'       => ['required','in:pending,reviewing,accepted,in_progress,rejected,completed'],
            'admin_note'   => ['nullable','string','max:1000'],
            'quoted_price' => ['nullable','numeric','min:0'],
        ]);

        $commission->update([
            'status'       => $request->status,
            'admin_note'   => $request->admin_note,
            'quoted_price' => $request->quoted_price ?? null,
        ]);

        return back()->with('success',"Status commission #{$commission->id} diperbarui.");
    }
}