<?php

$EM_CONF['twoh_tiny_png'] = [
    'title' => 'Tiny PNG',
    'description' => 'Optimize your TYPO3 Images with Tiny PNG',
    'category' => 'plugin',
    'author' => 'Andreas Reichel, Igor Smertin',
    'author_email' => 'a.reichel91@outlook.com, igor.smertin@web.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.5',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'php' => '>=8.0'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
