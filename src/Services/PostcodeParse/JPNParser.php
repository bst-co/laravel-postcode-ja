<?php

namespace BstCo\PostcodeJa\Services\PostcodeParse;

use BstCo\PostcodeJa\Models\PostCode;
use ErrorException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;

class JPNParser extends ParseBase implements ParseInterface
{
    /**
     * @throws ErrorException
     */
    public function __construct(
        File $file
    ) {
        parent::__construct($file);

        if ($this->file->getExtension() !== 'csv') {
            throw new ErrorException("File '{$this->file->getBasename()}' is not csv.");
        }
    }

    public function parsing(): void
    {
        PostCode::whereCountryCode($this->countryCode())->delete();

        $reader = new SplFileObject($this->file->getPathname());
        $reader->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE);

        foreach ($reader as $row) {
            $datum = [
                'gov_id' => $row[0],
                'zip_code' => $row[2],
                'state' => $row[6],
                'city' => $row[7],
                'town' => $row[8],
                'update' => (int) $row[13],
                'change' => (int) $row[14],
                'state_id' => null,
                'city_id' => null,
            ];

            $datum = Arr::map($datum, fn ($value) => Str::squish($value));
            $datum = Arr::map($datum, fn ($value) => mb_convert_kana($value, 'aKV'));

            if ($datum['town'] === '以下に掲載がない場合') {
                $datum['town'] = '';
            }

            if (preg_match('/^(\d{2})(\d{3})$/', $datum['gov_id'], $matches)) {
                $datum['state_id'] = $matches[1];
                $datum['city_id'] = $matches[2];
            }

            if (preg_match('/(.+)\(.*?\)/', $datum['town'], $matches)) {
                $datum['town'] = $matches[1];
            }

            $model = (new PostCode)
                ->fill([
                    'country_code' => $this->countryCode(),
                    'zip_code' => $datum['zip_code'],
                    'state_id' => $datum['state_id'],
                    'city_id' => $datum['city_id'],
                    'state' => $datum['state'],
                    'city' => $datum['city'],
                    'address' => $datum['town'],
                ]);

            if ($datum['change'] === '6') {
                $model->deleted_at = now();
            } else {
                $model->deleted_at = null;
            }

            $this->push($model);
        }
    }
}
