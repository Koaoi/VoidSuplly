<?php
// app/Http/Controllers/Admin/ReviewController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user','product'])->latest();

        if ($request->filled('approved')) {
            $query->where('is_approved',$request->approved === '1');
        }

        $reviews = $query->paginate(20)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggleApprove(Review $review)
    {
        $review->update(['is_approved' => !$review->is_approved]);
        return back()->with('success','Status review berhasil diperbarui.');
    }

    public function destroy(Review $review)
    {
        if ($review->image) Storage::disk('public')->delete($review->image);
        $review->delete();
        return back()->with('success','Review berhasil dihapus.');
    }
}