<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function test_the_landing_page_redirects_guests_to_login(): void
    {
        $response = $this->get(route('landing'));

        $response->assertRedirectToRoute('login');
    }
}
