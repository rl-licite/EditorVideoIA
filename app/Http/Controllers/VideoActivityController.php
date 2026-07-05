<?php

namespace App\Http\Controllers;

use App\Models\VideoActivity;
use Illuminate\Support\Facades\Auth;

class VideoActivityController extends Controller
{
    public function index()
    {
        $activities = VideoActivity::where('user_id', Auth::id())
            ->with('video')
            ->latest()
            ->paginate(20);

        return view('activities.index', compact('activities'));
    }
}
