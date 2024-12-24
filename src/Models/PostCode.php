<?php

namespace BstCo\PostcodeJa\Models;

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
        'zip_code',
        'state_id',
        'city_id',
        'state',
        'city',
        'address',
    ];

    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Search Post Code
     * @param Builder|PostCode $query
     * @param string $zip_code Search Zip Code
     * @param string|null $country_code Country Code (default = config.postcode.country.default)
     * @return Builder|PostCode
     * @noinspection PhpUnused
     */
    public function scopeSearch(Builder|PostCode $query, string $zip_code, ?string $country_code = null): Builder|PostCode
    {
        return $query
            ->where('zip_code', 'like', $zip_code.'%')
            ->where('country_code', '=', $country_code ?? config('postcode.country.default'));
    }
}
