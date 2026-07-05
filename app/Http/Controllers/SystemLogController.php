<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SystemLogController extends Controller
{
    public function index()
    {
        $logs = SystemLog::where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhereNull('user_id');
            })
            ->latest()
            ->paginate(30);

        return view('logs.index', compact('logs'));
    }
}
