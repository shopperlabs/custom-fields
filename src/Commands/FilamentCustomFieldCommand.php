<?php

namespace ManukMinasyan\FilamentCustomField\Commands;

use Illuminate\Console\Command;

class FilamentCustomFieldCommand extends Command
{
    public $signature = 'filament-custom-field';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
