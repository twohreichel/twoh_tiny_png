<?php

$EM_CONF['twoh_tiny_png'] = [
    'title' => 'Tiny PNG',
    'description' => 'Optimize your TYPO3 Images using the TinyPNG API',
    'category' => 'plugin',
    'author' => 'Andreas Reichel, Igor Smertin',
    'author_email' => 'a.reichel91@outlook.com, igor.smertin@web.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.9',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'php' => '8.2.0-8.3.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
