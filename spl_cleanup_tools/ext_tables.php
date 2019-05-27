<?php

defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'SPL.SplCleanupTools',
        'tools',
        'Cleanup',
        '',
        [
            'Cleanup' => 'index,cleanup'
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:spl_cleanup_tools/ext_icon.svg',
            'labels' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}