<?php

namespace ManukMinasyan\FilamentCustomField\Commands;

use Illuminate\Console\Command;

class FilamentAttributeCommand extends Command
{
    public $signature = 'filament-attribute';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
