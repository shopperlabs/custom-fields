<?php

namespace Relaticle\CustomFields\Migrations;

use Illuminate\Database\Migrations\Migration;

abstract class CustomFieldsMigration extends Migration
{
    protected CustomFieldsMigrator $migrator;

    abstract public function up();

    public function __construct()
    {
        $this->migrator = app(CustomFieldsMigrator::class);
    }
}
