<?php

namespace AvocetShores\Conduit\Commands;

use Illuminate\Console\Command;

class ConduitCommand extends Command
{
    public $signature = 'laravel-conduit';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
