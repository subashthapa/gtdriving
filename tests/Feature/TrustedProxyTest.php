<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_assets_use_https_behind_the_reverse_proxy(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '172.18.0.2',
        ])->withHeaders([
            'X-Forwarded-Host' => 'gtdriving.test',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ])->get('/');

        $response->assertOk();
        $response->assertSee('https://gtdriving.test/build/assets/', false);
        $response->assertDontSee('http://gtdriving.test/build/assets/', false);
    }
}
