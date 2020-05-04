<?php

defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    // get extension configuration
    $extensionConfiguration = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('cleanup_tools');
    
    // Register toolbar item
    if ($extensionConfiguration['enableBackendModule']) {
        TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule('ChristianReifenscheid.CleanupTools',
            'tools', 
            'Cleanup',
            '',
            [
                'Cleanup' => 'index,cleanup',
                'Ajax' => 'main',
                'History' => 'index,cleanup'
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:cleanup_tools/ext_icon.svg',
                'labels' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf'
            ]
            );
        
        // Register CSS
        $GLOBALS['TBE_STYLES']['skins']['cleanup_tools'] = [
            'name' => 'Cleanup tools',
            'stylesheetDirectories' => [
                'css' => 'EXT:cleanup_tools/Resources/Public/Css/'
            ]
        ];
    }
}