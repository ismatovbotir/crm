<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * `/` is a permanent redirect to `/admin` (see routes/web.php), not a 200 response.
     * The default Laravel stub assumed a welcome page that no longer exists in RSG-CRM.
     */
    public function test_the_root_url_redirects_to_admin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }
}
