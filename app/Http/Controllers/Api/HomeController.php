<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){
        $users = User::count();
        $posts = Post::count();
        $users_hasnot_posts = User::whereDoesntHave('posts')->count();
        return response()->json([
            'status' => true,
            'users' => $users,
            'posts' => $posts,
            'users_have_not_posts' => $users_hasnot_posts,
        ]);
    }
}
