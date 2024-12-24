<?php

namespace BstCo\PostcodeJa\Test\Unit;

use BstCo\PostcodeJa\Models\PostCode;
use BstCo\PostcodeJa\BstCoPostcodeJaServiceProvider;
use BstCo\PostcodeJa\Providers\PostcodeJaServiceProvider;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase;

class PostCodeCommandTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    protected function getPackageProviders($app): array
    {
        return [PostcodeJaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'sync');

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function test_migration()
    {
        PostCode::first();
        $this->assertTrue(true);
    }

    public function test_basic()
    {
        $this->artisan('postcode:create');

        $values = [
            '7202124' => [
                'state' => '広島県',
                'city' => '福山市',
                'address' => '神辺町川南',
            ],
            '0788202' => [
                'state' => '北海道',
                'city' => '旭川市',
                'address' => '東旭川町豊田',
            ],
            '5980000' => [
                'state' => '大阪府',
                'city' => '泉南郡田尻町',
                'address' => '',
            ],
        ];

        foreach ($values as $zip_code => $value) {
            $model = PostCode::search($zip_code)->first();

            $this->assertEquals($model->zip_code, $zip_code);
            $this->assertEquals($model->state, $value['state']);
            $this->assertEquals($model->city, $value['city']);
            $this->assertEquals($model->address, $value['address']);
        }
    }
}
