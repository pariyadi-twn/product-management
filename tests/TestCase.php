<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            $this->fail('Konfigurasi cache aktif. Jalankan `php artisan config:clear` sebelum test agar database test tidak tertukar dengan database pengembangan.');
        }

        parent::setUp();

        $this->withoutVite();
    }
}
