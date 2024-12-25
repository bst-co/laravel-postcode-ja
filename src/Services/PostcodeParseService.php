<?php

namespace BstCo\PostcodeJa\Services;

use BstCo\PostcodeJa\Exceptions\CountryCodeException;
use BstCo\PostcodeJa\Exceptions\ParsingException;
use BstCo\PostcodeJa\Services\PostcodeParse\ParseInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

class PostcodeParseService
{
    protected const HASH_ALGORITHM = 'sha1';

    /**
     * サービスが利用するストレージ
     */
    private readonly Filesystem $storage;

    private readonly Country $country;

    /**
     * 処理が終わったら削除するファイルリスト
     */
    protected array $invokes = [];

    /**
     * 処理対象国コード
     *
     * @throws ParsingException
     * @throws CountryCodeException
     */
    public function __construct(
        string $country_code,
        public readonly bool $force = false,
    ) {
        $this->country = Country::safeMake($country_code);

        $this->storage = Storage::build([
            'driver' => 'local',
            'root' => storage_path('postcode'),
        ]);

        if (empty($this->country->source)) {
            throw new ParsingException('source not found');
        }
    }

    /**
     * @throws ParsingException
     */
    public function run(): void
    {
        $file = $this->download();
        $file = $this->expand($file);

        $parsers = config('postcode.parsers');

        $interface = data_get($parsers, $this->country->code);

        if (is_a($interface, ParseInterface::class, true)) {
            (new $interface($file))->parse();

            return;
        }

        throw new ParsingException("source#{$this->country->code} Parser not defined.");
    }

    /**
     * 圧縮ファイルの場合は展開してファイルを抽出
     *
     * @throws ParsingException
     */
    protected function expand(File $file): File
    {
        $mime = $file->getMimeType();
        $expand_dir = $this->storage->path($this->country->code).'.expand';

        if ($this->storage->exists($expand_dir)) {
            $this->storage->deleteDirectory($expand_dir);
        }

        if ($mime === 'application/zip') {
            $zip = new ZipArchive;
            if ($zip->open($file->getPathname())) {
                $zip->extractTo($expand_dir);
                $zip->close();
            }
        } else {
            return $file;
        }

        $expand_file = $this->country->expand;

        if (empty($expand_file)) {
            throw new ParsingException("source#{$this->country->code} is expand file not defined");
        }

        $expand_path = $expand_dir.DIRECTORY_SEPARATOR.$expand_file;

        if (! file_exists($expand_path)) {
            throw new ParsingException("source#{$this->country->code} is expand file '$expand_file' not found");
        }

        return new File($expand_path);
    }

    /**
     * 対象ファイルをダウンロードする
     *
     * @return File|null
     *
     * @throws ParsingException
     */
    protected function download(): ?File
    {
        if (empty($this->country->source)) {
            throw new ParsingException("source#{$this->country->code} is url not found");
        }

        $local_path = $this->country->code.'.raw';
        $real_path = $this->storage->path($local_path);

        if (file_exists($real_path) && filectime($real_path) > time() - 24 * 60 * 60 && ! $this->force) {
            $file = new File($real_path);
        } else {

            $resource = fopen($this->country->source, 'r');

            $this->storage->put($local_path, $resource);

            fclose($resource);

            $file = new File($real_path);
            $hash = $this->calcHash($file);

            if ($this->getHash() === $hash && ! $this->force) {
                return null;
            }

            $this->setHash($hash);
        }

        return $this->expand($file);
    }

    protected function hashPath(): string
    {
        return $this->country->code.'.'.static::HASH_ALGORITHM;
    }

    protected function getHash(): string
    {
        return $this->storage->exists($this->hashPath()) ? $this->storage->get($this->hashPath()) : '';
    }

    protected function setHash(string $hash): bool
    {
        return $this->storage->put($this->hashPath(), $hash);
    }

    protected function calcHash(File $file): string
    {
        return hash_file(static::HASH_ALGORITHM, $file->getPathname());
    }
}
