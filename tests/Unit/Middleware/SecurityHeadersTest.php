<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Middleware\SecurityHeaders;

class SecurityHeadersTest extends TestCase
{
    protected SecurityHeaders $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeaders();
    }

    /** @test */
    public function it_adds_xss_protection_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    /** @test */
    public function it_adds_content_type_options_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    /** @test */
    public function it_adds_frame_options_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    /** @test */
    public function it_adds_referrer_policy_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(
            'strict-origin-when-cross-origin',
            $response->headers->get('Referrer-Policy')
        );
    }

    /** @test */
    public function it_adds_csp_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
    }
}
