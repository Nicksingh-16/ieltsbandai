<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PricingController extends Controller
{
    public function index()
    {
        return view('pricing');
    }
}
