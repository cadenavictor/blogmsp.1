<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppConfigurationTest extends TestCase
{
    public function test_application_url_is_used_for_absolute_blog_urls(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $this->assertSame('http://127.0.0.1:8000/posts/example', url('/posts/example'));
    }
}
