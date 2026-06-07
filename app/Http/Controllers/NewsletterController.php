<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Validasi email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->email;

        // Simpan ke session (tanpa database)
        $subscribers = session()->get('newsletter_subscribers', []);
        
        if (in_array($email, $subscribers)) {
            return redirect()->back()->with('error', 'Email sudah terdaftar!');
        }

        $subscribers[] = $email;
        session()->put('newsletter_subscribers', $subscribers);

        // Redirect dengan pesan sukses
        return redirect()->back()->with('success', 'Terima kasih ' . $email . '! Kamu sekarang berlangganan.');
    }
}