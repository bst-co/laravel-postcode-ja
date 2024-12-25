<?php

namespace BstCo\PostcodeJa\Test\Unit;

use BstCo\PostcodeJa\Providers\PostcodeJaServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
abstract class TestCase extends OrchestraTestCase {


    protected function getPackageProviders($app): array
    {
        return [PostcodeJaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'sync');

        $app['config']->set('postcode.database', 'testbench');

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}