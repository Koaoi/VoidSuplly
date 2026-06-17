<?php
// app/Http/Controllers/Admin/ReviewController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])->latest();

        if ($request->filled('approved')) {
            $query->where('is_approved', $request->approved === '1');
        }

        $reviews = $query->paginate(20)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Toggle review approval status.
     */
    public function toggleApprove(Review $review)
    {
        $review->update(['is_approved' => !$review->is_approved]);
        return back()->with('success', 'Status review berhasil diperbarui.');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        if ($review->image) {
            Storage::disk('public')->delete($review->image);
        }
        $review->delete();
        return back()->with('success', 'Review berhasil dihapus.');
    }

    /**
     * ⭐ ADMIN REPLY TO REVIEW ⭐
     * Admin membalas review dari customer
     */
    public function reply(Request $request, Review $review)
    {
        $request->validate([
            'admin_reply' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'admin_reply' => $request->admin_reply,
            'admin_reply_updated_at' => now(),
        ]);

        if ($request->admin_reply) {
            return back()->with('success', 'Balasan berhasil dikirim!');
        } else {
            return back()->with('success', 'Balasan berhasil dihapus!');
        }
    }
}