<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogUpdateRequest;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\BlogResource;

class BlogChangeStateController extends Controller
{
    public function changeStateBlog(Blog $blog)
    {

        $blog->is_active = !$blog->is_active;
        $blog->save();
        $blogResource = BlogResource::make($blog);
        $data = [
            'message' => 'Cambio de estado correctamente',
            'blog' => $blogResource,
            'code' => 200,
        ];
        return response()->json($data);
    }
}
