<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_branded_error_views_render_for_all_supported_statuses(): void
    {
        foreach ([403, 404, 419, 429, 500, 503] as $status) {
            $html = view("errors.{$status}")->render();

            $this->assertStringContainsString('error-card', $html);
            $this->assertStringContainsString((string) $status, $html);
            $this->assertStringContainsString(__('app_name'), $html);
        }
    }
}
