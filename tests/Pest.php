<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Relaticle\CustomFields\Tests\TestCase;

require_once __DIR__.'/Helpers.php';

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);