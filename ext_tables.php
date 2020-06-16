<?php

defined('TYPO3_MODE') or die();

(function ($extKey) {
    
    if (TYPO3_MODE === 'BE') {
        // get extension configuration
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('cleanup_tools');
        
        // Register toolbar item
        if ($extensionConfiguration['enableBackendModule']) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'CleanupTools',
                'tools',
                'tx_cleanuptools',
                '',
                [
                    \ChristianReifenscheid\CleanupTools\Controller\CleanupController::class => 'index, cleanup',
                    \ChristianReifenscheid\CleanupTools\Controller\AjaxController::class => 'main',
                    \ChristianReifenscheid\CleanupTools\Controller\HistoryController::class => 'index,cleanup'
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
    
})('cleanup_tools');