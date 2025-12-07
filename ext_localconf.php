<?php

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

defined('TYPO3') || die('Access denied.');

// Configure logging for the TwohTinyPng extension
$GLOBALS['TYPO3_CONF_VARS']['LOG']['TWOH']['TwohTinyPng']['writerConfiguration'] = [
    LogLevel::INFO => [
        FileWriter::class => [
            'logFile' => Environment::getVarPath() . '/log/typo3_tinypng_upload.log',
        ],
    ],
    LogLevel::WARNING => [
        FileWriter::class => [
            'logFile' => Environment::getVarPath() . '/log/typo3_tinypng_upload.log',
        ],
    ],
    LogLevel::ERROR => [
        FileWriter::class => [
            'logFile' => Environment::getVarPath() . '/log/typo3_tinypng_upload.log',
        ],
    ],
];