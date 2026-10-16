<?php

namespace Tests\Feature\Blog;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Blog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogUpdateAndChangeStateControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBlogMsSchema();
        $this->withoutMiddleware([AuthenticateJwt::class, RoleMiddleware::class]);
        Storage::fake('azure');
        config([
            'filesystems.disks.azure.url' => 'https://fakeazure.blob.core.windows.net',
            'filesystems.disks.azure.container' => 'blogs-container',
        ]);
    }

    public function test_admin_can_update_existing_blog_and_replace_image(): void
    {
        $blog = Blog::create([
            'title' => 'Blog inicial',
            'description' => 'Descripción inicial',
            'url' => 'https://universidad.edu.co/blogs/inicial',
            'image' => 'https://fakeazure.blob.core.windows.net/blogs-container/images/old.png',
            'is_active' => true,
        ]);

        $response = $this->patchJson("/api/{$blog->id}/blogs_web", [
            'title' => 'Blog actualizado',
            'description' => 'Descripción actualizada para la comunidad universitaria',
            'url' => 'https://universidad.edu.co/blogs/actualizado',
            'image' => UploadedFile::fake()->create('new-blog.png', 120, 'image/png'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Se ha actualizado el blog correctamente')
            ->assertJsonPath('blog.title', 'Blog actualizado')
            ->assertJsonPath('blog.url', 'https://universidad.edu.co/blogs/actualizado');

        $this->assertDatabaseHas('blogs', [
            'id' => $blog->id,
            'title' => 'Blog actualizado',
            'description' => 'Descripción actualizada para la comunidad universitaria',
            'url' => 'https://universidad.edu.co/blogs/actualizado',
        ]);
    }

    public function test_admin_can_toggle_blog_active_state(): void
    {
        $blog = Blog::create([
            'title' => 'Economía circular en el campus',
            'description' => 'Conoce las iniciativas del campus',
            'url' => 'https://universidad.edu.co/blogs/economia-circular',
            'image' => 'https://fakeazure.blob.core.windows.net/blogs-container/images/blog.png',
            'is_active' => true,
        ]);

        $response = $this->patchJson("/api/{$blog->id}/change_state_blogs");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cambio de estado correctamente');

        $this->assertDatabaseHas('blogs', [
            'id' => $blog->id,
            'is_active' => 0,
        ]);
    }
}
