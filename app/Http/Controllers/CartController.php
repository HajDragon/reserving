<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        return view('carts.index', [
            'user' => $request->user(),
        ]);
    }
}
