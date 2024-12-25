<?php

namespace BstCo\PostcodeJa\Test\Unit;

use BstCo\PostcodeJa\Services\Country;

class CountryTest extends TestCase
{
    public function test_get()
    {
        $this->assertEquals(Country::get('jp'), 'jp');
        $this->assertEquals(Country::get('EN'), 'en');
        $this->assertEquals(Country::get('sg'), 'sg');
        $this->assertEquals(Country::get('tt'), 'tt');
        $this->assertEquals(Country::safeGet(), config('postcode.country.default'));
        $this->assertEquals(Country::safeGet('us'), 'us');
    }

    public function test_safe_get()
    {
        $this->assertThrows(fn () => Country::safeGet('aa'));
        $this->assertThrows(fn () => Country::safeGet('bb'));
    }

    public function test_format()
    {
        $this->assertEquals('000-0000', Country::make('jp')->format('0000000'));
        $this->assertEquals('12345', Country::make('us')->format('12345'));
        $this->assertEquals('12345-6789', Country::make('us')->format('123456789'));
        $this->assertEquals('12345-6789', Country::make('us')->format('12345-6789'));
        $this->assertEquals('000 000', Country::make('ca')->format('000000'));
        $this->assertEquals('A00 Z0W', Country::make('ca')->format('A00z0w'));
    }
}
