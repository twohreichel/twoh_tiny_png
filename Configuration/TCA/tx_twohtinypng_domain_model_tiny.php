<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:twoh_tiny_png/Resources/Private/Language/locallang.xlf:tx_twohtinypng_domain_model_tiny',
        'label' => 'identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'identifier',
        'iconfile' => 'EXT:twoh_tiny_png/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, identifier, dimension',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'identifier' => [
            'exclude' => true,
            'label' => 'LLL:EXT:twoh_tiny_png/Resources/Private/Language/locallang.xlf:identifier',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'dimension' => [
            'exclude' => true,
            'label' => 'LLL:EXT:twoh_tiny_png/Resources/Private/Language/locallang.xlf:dimension',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
    ],
];
