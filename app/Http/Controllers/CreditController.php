<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use Illuminate\Support\Facades\Auth;

class CreditController extends Controller
{
    public function index()
    {
        $transactions = CreditTransaction::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('credits.index', compact('transactions'));
    }
}
