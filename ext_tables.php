<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    // get extension configuration
    $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('spl_cleanup_tools');
    
    // Register toolbar item
    if ($extensionConfiguration['enableBackendModule']) {
        ExtensionUtility::registerModule('SPL.SplCleanupTools', // extensionName
            'tools', // mainModuleName
            'Cleanup', // subModuleName
            '', // position
            [
                'Cleanup' => 'index,cleanup',
                'Ajax' => 'main',
                'History' => 'index,cleanup'
            ], // controllerActions
            [
                'access' => 'admin',
                'icon' => 'EXT:spl_cleanup_tools/ext_icon.svg',
                'labels' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf'
            ] // moduleConfiguration
            );
        
        // Register CSS
        $GLOBALS['TBE_STYLES']['skins']['spl_cleanup_tools'] = [
            'name' => 'Cleanup tools',
            'stylesheetDirectories' => [
                'css' => 'EXT:spl_cleanup_tools/Resources/Public/Css/'
            ]
        ];
    }
}