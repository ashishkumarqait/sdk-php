<?php

namespace Tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase as PHPUnit;

class TestCase extends PHPUnit
{
    /**
     * Set up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }
}
