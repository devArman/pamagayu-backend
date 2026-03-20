<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => Post::count(),
            'published' => Post::published()->count(),
            'drafts' => Post::where('status', 'draft')->count(),
            'videos' => Post::ofType('video')->count(),
            'images' => Post::ofType('image')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
