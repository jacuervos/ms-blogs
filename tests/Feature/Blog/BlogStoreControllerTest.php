<?php

namespace Tests\Feature\Blog;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Blog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogStoreControllerTest extends TestCase
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

    public function test_admin_can_create_blog_with_image(): void
    {
        $response = $this->postJson('/api/blogs_web', [
            'title' => 'Reciclaje en casa',
            'description' => 'Guía práctica para separar residuos desde el hogar',
            'url' => 'https://universidad.edu.co/blogs/reciclaje-en-casa',
            'image' => UploadedFile::fake()->create('blog.png', 100, 'image/png'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Se ha creado el blog correctamente')
            ->assertJsonPath('blog.title', 'Reciclaje en casa')
            ->assertJsonPath('blog.url', 'https://universidad.edu.co/blogs/reciclaje-en-casa');

        $this->assertDatabaseHas('blogs', [
            'title' => 'Reciclaje en casa',
            'description' => 'Guía práctica para separar residuos desde el hogar',
            'url' => 'https://universidad.edu.co/blogs/reciclaje-en-casa',
        ]);

        $blog = Blog::first();
        $this->assertNotNull($blog);
        $this->assertStringStartsWith(
            'https://fakeazure.blob.core.windows.net/blogs-container/images/',
            $blog->image
        );
    }

    public function test_store_blog_requires_mandatory_fields(): void
    {
        $response = $this->postJson('/api/blogs_web', [
            'title' => '',
            'description' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'url']);
    }
}
