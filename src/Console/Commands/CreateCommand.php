<?php

namespace BstCo\PostcodeJa\Console\Commands;

use BstCo\PostcodeJa\Services\PostcodeParseService;
use Illuminate\Console\Command;

class CreateCommand extends Command
{
    protected $signature = 'postcode:create {country_code? : Country Code} {--force : Force to overwrite}';

    protected $description = 'Create postcodes for a specified country.';

    public function handle(): void
    {
        $country_code = $this->argument('country_code');
        $force = (bool) $this->option('force');

        if ($country_code) {
            $this->exec($country_code, $force);

            return;
        }

        $sources = config('postcode.source', []);

        foreach (array_keys($sources) as $country_code) {
            $this->exec($country_code, $force);
        }
    }

    protected function exec(string $country_code, bool $force = false): void
    {
        (new PostcodeParseService($country_code, $force))->run();
    }
}
