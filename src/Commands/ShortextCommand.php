<?php

namespace NyCorp\Shortext\Commands;

use Illuminate\Console\Command;

class ShortextCommand extends Command
{
    public $signature = 'shortext-php';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
