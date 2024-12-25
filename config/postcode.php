<?php

use BstCo\PostcodeJa\Services\PostcodeParse\JPNParser;

return [
    'country' => [
        'default' => 'jp',
    ],

    'database' => env('POSTCODE_DATABASE'),

    'sources' => [
        // Japan
        'jp' => [
            // DataSource URL
            'source' => 'https://www.post.japanpost.jp/zipcode/dl/utf/zip/utf_ken_all.zip',
            // Expanded file name
            'expand' => 'utf_ken_all.csv',
            // Post code length
            'length' => 7,
            // Post code padding string
            'padding' => '0',
            // Post code separating format
            'format' => [3, 4],
            // Post code separator
            'separator' => '-',
        ],
        // United of State
        'us' => [
            'length' => [5, 9],
            'padding' => '0',
            'format' => [5, 4],
            'separator' => '-',
        ],
        // Canada
        'ca' => [
            'length' => 6,
            'padding' => '0',
            'format' => [3, 3],
            'separator' => ' ',
        ],
        // United Kingdom of Great Britain and Northern Ireland
        'gb' => [
            'length' => 6,
            'padding' => '0',
            'format' => [3, 3],
            'separator' => ' ',
        ],
        // People's Republic of China
        'cn' => [
            'length' => 6,
            'padding' => '0',
            'format' => [6],
            'separator' => '',
        ],
        // Republic of Singapore
        'sg' => [
            'length' => 6,
            'padding' => '0',
            'format' => [6],
            'separator' => '',
        ]
    ],

    'source' => [
        'jp' => [
            'url' => 'https://www.post.japanpost.jp/zipcode/dl/utf/zip/utf_ken_all.zip',
            'file' => 'utf_ken_all.csv',
        ],
    ],

    'parsers' => [
        'jp' => JPNParser::class,
    ],
];
