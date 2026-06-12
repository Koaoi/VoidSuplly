<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function sizeGuide()
    {
        return view('pages.size-guide');
    }

    public function howToOrder()
    {
        return view('pages.how-to-order');
    }

    public function returns()
    {
        return view('pages.returns');
    }

    public function contact()
    {
        return view('pages.contact');
    }
}