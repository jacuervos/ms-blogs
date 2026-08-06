<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogUpdateRequest;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\BlogResource;

class BlogUpdateController extends Controller
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

    public function update(BlogUpdateRequest $blogUpdateRequest, Blog $blog)
    {
        $blog->title = $blogUpdateRequest->title;
        $blog->description = $blogUpdateRequest->description;
        $blog->url = $blogUpdateRequest->url;
        if ($blogUpdateRequest->hasFile('image')) {
            if ($blog->image) {
                $baseUrl = rtrim(config('filesystems.disks.azure.url'), '/') . '/' .
                    config('filesystems.disks.azure.container') . '/';
                $path = str_replace($baseUrl, '', $blog->image);
                Storage::disk('azure')->delete($path);
            }
            $blog->image = $this->saveStorage($blogUpdateRequest->image, 'blogs');
        }
        $blog->save();
        $blogResource = BlogResource::make($blog);
        $data = [
            'message' => 'Se ha actualizado el blog correctamente',
            'blog' => $blogResource,
            'code' => 200,
        ];
        return response()->json($data);
    }
}
