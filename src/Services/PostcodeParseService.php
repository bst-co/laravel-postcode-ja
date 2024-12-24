<?php

namespace BstCo\PostcodeJa\Services;

use BstCo\PostcodeJa\Services\PostcodeParse\ParseInterface;
use ErrorException;
use Exception;
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

    /**
     * ソースセット
     */
    private readonly array $source;

    /**
     * 処理が終わったら削除するファイルリスト
     */
    protected array $invokes = [];

    /**
     * 処理対象国コード
     *
     * @throws Exception
     */
    public function __construct(
        public readonly string $country_code,
        public readonly bool $force = false,
    ) {
        $this->storage = Storage::build([
            'driver' => 'local',
            'root' => storage_path('postcode'),
        ]);

        $source = config('postcode.source.'.$this->country_code);

        if (! is_array($source) || empty($source)) {
            throw new Exception('source not found');
        }

        $this->source = $source;
    }

    /**
     * @throws ErrorException
     */
    public function run(): void
    {
        $file = $this->download();
        $file = $this->expand($file);

        $parsers = config('postcode.parsers');

        $interface = data_get($parsers, strtoupper($this->country_code));

        if (is_a($interface, ParseInterface::class, true)) {
            (new $interface($file))->parse();

            return;
        }

        throw new ErrorException("source#{$this->country_code} Parser not defined.");
    }

    protected function expand(File $file)
    {
        $mime = $file->getMimeType();
        $expand_dir = $this->storage->path($this->country_code).'.expand';

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

        $expand_file = data_get($this->source, 'file');

        if (empty($expand_file)) {
            throw new ErrorException("source#{$this->country_code} is expand file not defined");
        }

        $expand_path = $expand_dir.DIRECTORY_SEPARATOR.$expand_file;

        if (! file_exists($expand_path)) {
            throw new ErrorException("source#{$this->country_code} is expand file '$expand_file' not found");
        }

        return new File($expand_path);
    }

    protected function download()
    {
        if (empty($this->source['url'])) {
            throw new ErrorException("source#{$this->country_code} is url not found");
        }

        $local_path = $this->country_code.'.raw';
        $real_path = $this->storage->path($local_path);

        if (file_exists($real_path) && filectime($real_path) > time() - 24 * 60 * 60 && ! $this->force) {
            $file = new File($real_path);
        } else {

            $resource = fopen($this->source['url'], 'r');

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
        return $this->country_code.'.'.static::HASH_ALGORITHM;
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
