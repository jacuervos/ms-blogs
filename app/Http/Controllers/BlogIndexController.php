<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Http\Resources\BlogResource;

class BlogIndexController extends Controller
{
    public function index()
    {
        $blogs = Blog::orderBy('id', 'DESC')
            ->paginate(10);
        return BlogResource::collection($blogs);
    }

    public function indexActive()
    {
        $blogs = Blog::where('is_active', true)
            ->orderBy('id', 'DESC')
            ->paginate(10);
        return BlogResource::collection($blogs);
    }

}
