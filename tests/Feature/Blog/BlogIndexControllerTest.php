<?php

namespace Tests\Feature\Blog;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Blog;
use Tests\TestCase;

class BlogIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBlogMsSchema();
        $this->withoutMiddleware([AuthenticateJwt::class, RoleMiddleware::class]);
    }

    public function test_user_can_list_only_active_blogs_in_app(): void
    {
        Blog::create([
            'title' => 'Blog activo 1',
            'description' => 'Visible para usuarios',
            'url' => 'https://universidad.edu.co/blogs/activo-1',
            'image' => 'https://fakeazure.blob.core.windows.net/blogs-container/images/1.png',
            'is_active' => true,
        ]);

        Blog::create([
            'title' => 'Blog inactivo',
            'description' => 'No visible en app',
            'url' => 'https://universidad.edu.co/blogs/inactivo',
            'image' => 'https://fakeazure.blob.core.windows.net/blogs-container/images/2.png',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/blogs_app');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Blog activo 1');
    }

    public function test_admin_can_list_all_blogs_for_web_panel(): void
    {
        Blog::create([
            'title' => 'Blog activo',
            'description' => 'Visible en web',
            'url' => 'https://universidad.edu.co/blogs/activo',
            'image' => 'https://fakeazure.blob.core.windows.net/blogs-container/images/1.png',
            'is_active' => true,
        ]);

        Blog::create([
            'title' => 'Blog inactivo',
            'description' => 'También visible para administrador en web',
            'url' => 'https://universidad.edu.co/blogs/inactivo',
            'image' => 'https://fakeazure.blob.core.windows.net/blogs-container/images/2.png',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/blogs_web');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
