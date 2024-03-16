<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

ExtensionManagementUtility::addStaticFile(
    'twoh_tiny_png',
    'Configuration/TypoScript',
    'Tiny PNG'
);
