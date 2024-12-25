<?php

namespace BstCo\PostcodeJa\Services;

use BstCo\PostcodeJa\Exceptions\CountryCodeException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;

class Country implements \JsonSerializable, Arrayable
{
    /**
     * 国コードを取得
     */
    public static function get(?string $country_code = null): string
    {
        return static::make($country_code)->code;
    }

    /**
     * 国コードを取得、未定義の場合は例外を返す
     *
     * @throws CountryCodeException
     */
    public static function safeGet(?string $country_code = null): string
    {
        return static::safeMake($country_code)->code;
    }

    public static function make(?string $country_code = null): Country
    {
        $key = __METHOD__.'::'.$country_code;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $object = new static($country_code);

        Cache::put($key, $object, 60);

        return $object;
    }

    /**
     * @throws CountryCodeException
     */
    public static function safeMake(?string $country_code = null): Country
    {
        $object = static::make($country_code);

        if ($object->exist) {
            return $object;
        }

        throw new CountryCodeException("Country code '$object->code' is not found.");
    }

    protected function __construct(
        ?string $country_code = null
    ) {
        $this->code = strtolower($country_code ?? config('postcode.country.default'));

        $sources = config('postcode.sources');

        $this->exist = isset($sources[$this->code]);

        $source = $sources[$this->code] ?? [];

        $this->source = data_get($source, 'source');
        $this->expand = data_get($source, 'expand');

        $length = data_get($source, 'length', 0);

        if (! is_array($length)) {
            $length = [$length];
        }

        $this->length = is_array($length) ? $length : [$length];
        $this->padding = data_get($source, 'padding', '0');
        $this->format = data_get($source, 'format', []);
        $this->separator = data_get($source, 'separator', ' ');
        $this->lower = (bool) data_get($source, 'lower', false);
    }

    public function sanitize(string $postcode): string
    {
        $value = str_replace($this->separator, '', $postcode);

        foreach ($this->length as $length) {
            if (strlen($value) <= $length) {
                $value = str_pad($value, $length, $this->padding);
                break;
            }
        }

        return $this->lower ? strtolower($value) : strtoupper($value);
    }

    public function separate(string $postcode): array
    {
        $postcode = $this->sanitize($postcode);

        $values = [];
        $offset = 0;

        foreach ($this->format as $value) {
            $value = substr($postcode, $offset, $value);

            if ($value === '') {
                break;
            }

            $offset += strlen($value);
            $values[] = $value;
        }

        return count($values) > 0 ? $values : [$postcode];
    }

    public function format(string $postcode): string
    {
        return implode($this->separator, $this->separate($postcode));
    }

    /**
     * Country Code (Alpha-2)
     */
    public readonly string $code;

    /**
     * Config exist
     */
    public readonly bool $exist;

    /**
     * DataSource URL
     */
    public readonly ?string $source;

    /**
     * Expand file name
     *
     * @var string|array|mixed|null
     */
    public readonly ?string $expand;

    /**
     * Address Length
     *
     * @var array<int>
     */
    public readonly array $length;

    /**
     * Postcode Padding string
     */
    public readonly string $padding;

    /**
     * Postcode separating format
     *
     * @var int[]
     */
    public readonly array $format;

    /**
     * Postcode Separator
     */
    public readonly string $separator;

    /**
     * Postcode to lower case
     */
    public readonly bool $lower;

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'length' => $this->length,
            'padding' => $this->padding,
            'format' => $this->format,
            'separator' => $this->separator,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
