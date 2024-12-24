<?php

use BstCo\PostcodeJa\Services\PostcodeParse\JPNParser;

return [
    'country' => [
        'default' => 'JPN',
    ],

    'source' => [
        'JPN' => [
            'url' => 'https://www.post.japanpost.jp/zipcode/dl/utf/zip/utf_ken_all.zip',
            'file' => 'utf_ken_all.csv',
        ],
    ],

    'parsers' => [
        'JPN' => JPNParser::class,
    ]
];
