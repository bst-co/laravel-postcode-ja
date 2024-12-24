<?php

namespace BstCo\PostcodeJa\Services\PostcodeParse;

use Symfony\Component\HttpFoundation\File\File;

interface ParseInterface
{
    public function __construct(File $file);

    public function parse();
}