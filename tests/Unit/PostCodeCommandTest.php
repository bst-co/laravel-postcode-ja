<?php

namespace BstCo\PostcodeJa\Test\Unit;

use BstCo\PostcodeJa\Models\PostCode;
use Illuminate\Foundation\Testing\WithFaker;

class PostCodeCommandTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
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
                'formatted' => '720-2124',
                'state' => '広島県',
                'city' => '福山市',
                'address' => '神辺町川南',
            ],
            '0788202' => [
                'formatted' => '078-8202',
                'state' => '北海道',
                'city' => '旭川市',
                'address' => '東旭川町豊田',
            ],
            '5980000' => [
                'formatted' => '598-0000',
                'state' => '大阪府',
                'city' => '泉南郡田尻町',
                'address' => '',
            ],
        ];

        foreach ($values as $zip_code => $value) {
            $model = PostCode::search($zip_code)->first();

            $this->assertEquals($model->postcode, $zip_code);
            $this->assertEquals($model->postcode_formatted, $value['formatted']);
            $this->assertEquals($model->state, $value['state']);
            $this->assertEquals($model->city, $value['city']);
            $this->assertEquals($model->address, $value['address']);
        }
    }
}
