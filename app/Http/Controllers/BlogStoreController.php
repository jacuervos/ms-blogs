<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogStoreRequest;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\BlogResource;

class BlogStoreController extends Controller
{

    public function saveStorage($image, $route): string
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $safeName = Str::slug($originalName, '_');
        $imageName = $safeName . '.' . $extension;
        $nameRoute = 'images/' . $route . '/';
        Storage::disk('azure')->putFileAs($nameRoute, $image, $imageName);
        $url = rtrim(config('filesystems.disks.azure.url'), '/') . '/' .
            config('filesystems.disks.azure.container') . '/' .
            $nameRoute . $imageName;
        return $url;
    }

    public function store(BlogStoreRequest $blogStoreRequest)
    {
        $blog = New Blog();
        $blog->title = $blogStoreRequest->title;
        $blog->description = $blogStoreRequest->description;
        $blog->url = $blogStoreRequest->url;
        $blog->is_active = true;
        $image = $this->saveStorage($blogStoreRequest->images, 'blogs');
        $blog->image = $image;
        $blog->save();

        $blogResource = BlogResource::make($blog);
        $data = [
            'message' => 'Se ha creado el blog correctamente',
            'user' => $blogResource,
            'code' => 200,
        ];
        return response()->json($data);
    }
}
