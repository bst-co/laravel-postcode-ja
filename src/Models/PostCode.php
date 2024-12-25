<?php

namespace BstCo\PostcodeJa\Models;

use BstCo\PostcodeJa\Services\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostCode extends Model
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'country_code',
        'postcode',
        'postcode_formatted',
        'state_id',
        'city_id',
        'state',
        'city',
        'address',
    ];

    /**
     * {@inheritdoc}
     */
    public function getConnectionName()
    {
        return config('postcode.database', parent::getConnectionName());
    }

    /**
     * Search Post Code
     *
     * @param  string  $postcode  Search PostalCode
     * @param  string|null  $country_code  Country Code (default = config.postcode.country.default)
     *
     * @noinspection PhpUnused
     */
    public function scopeSearch(Builder|PostCode $query, string $postcode, ?string $country_code = null): Builder|PostCode
    {
        $country = Country::make($country_code);

        return $query
            ->where('postcode_formatted', '=', $country->format($postcode))
            ->where('country_code', '=', $country->code);
    }
}
