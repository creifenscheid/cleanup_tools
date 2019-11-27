<?php

defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        $extensionName = 'SPL.SplCleanupTools',
        $mainModuleName = 'tools',
        $subModuleName = 'Cleanup',
        $position = '',
        $controllerActions = [
            'Cleanup' => 'index,cleanup',
            'Toolbar' => 'main'
        ],
        $moduleConfiguration = [
            'access' => 'admin',
            'icon' => 'EXT:spl_cleanup_tools/ext_icon.svg',
            'labels' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}