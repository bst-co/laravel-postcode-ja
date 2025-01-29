<?php

namespace BstCo\PostcodeJa\Services\PostcodeParse;

use BstCo\PostcodeJa\Models\PostCode;
use BstCo\PostcodeJa\Services\Country;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\File;

abstract class ParseBase implements ParseInterface
{
    protected readonly Country $country;

    private ?Collection $collect = null;

    /**
     * @throws \BstCo\PostcodeJa\Exceptions\CountryCodeException
     */
    public function __construct(
        protected readonly File $file
    ) {
        $this->country = Country::safeMake($this->countryCode());
    }

    final public function parse(): void
    {
        $this->parsing();

        $this->pull();

        // 削除された項目を強制排除
        PostCode::whereCountryCode($this->country->code)
            ->onlyTrashed()
            ->forceDelete();
    }

    abstract protected function parsing(): void;

    /**
     * 国コード取得
     */
    protected function countryCode(): string
    {
        return (string) config('postcode.country.default');
    }

    /**
     * オブジェクトリストをDBにプッシュする件数の閾値
     */
    protected function threshold(): int
    {
        return 200;
    }

    /**
     * PostalCodeオブジェクトを保持します。保持数が閾値を超えた場合にデータベースにプッシュします。
     */
    final protected function push(PostCode $model): int
    {
        if ($this->collect === null) {
            $this->clear();
        }

        $this->collect->push($model);

        if ($this->collect->count() >= $this->threshold()) {
            return $this->pull();
        }

        return 0;
    }

    /**
     * 住所モデルをDBに対してUpsertします
     */
    final protected function pull(): int
    {
        $count = PostCode::upsert(
            $this->collect->toArray(),
            ['postcode', 'country_code'],
            (new PostCode)->getFillable(),
        );

        $this->clear();

        return $count;
    }

    /**
     * モデルオブジェクトのリストを初期化する
     */
    final protected function clear(): void
    {
        $this->collect = new Collection;
    }
}
