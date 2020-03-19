<?php

use SPL\SplCleanupTools\Backend\Toolbar\CleanUpToolbarItem;
use SPL\SplCleanupTools\Hooks\AfterDatabaseOperationsHook;
use SPL\SplCleanupTools\Hooks\DrawItemHook;
use SPL\SplCleanupTools\Task\CleanupAdditionalFieldProvider;
use SPL\SplCleanupTools\Task\CleanupTask;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') or die();

// Register icons
$iconRegistry = GeneralUtility::makeInstance(
    IconRegistry::class
);

$iconRegistry->registerIcon(
    'tx-splcleanuptools-icon',
    SvgIconProvider::class,
    ['source' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_icon.svg']
);


$iconRegistry->registerIcon(
    'tx-splcleanuptools-restore',
    SvgIconProvider::class,
    ['source' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_restore.svg']
);

// get extension configuration
$extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('spl_cleanup_tools');

// Register toolbar item
if ($extensionConfiguration['enableToolbarItem']) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433112] = CleanUpToolbarItem::class;
}

// HOOK: After database operations hook
if ($extensionConfiguration['enableAfterDatabaseOperationsHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['spl_cleanup_tools'] = AfterDatabaseOperationsHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['spl_cleanup_tools'] = AfterDatabaseOperationsHook::class;
}

// HOOK: DrawItem
if ($extensionConfiguration['enableDrawItemHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] = DrawItemHook::class;
}

// TASK
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][CleanupTask::class] = [
    'extension' => 'spl_cleanup_tools',
    'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.title',
    'description' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.description',
    'additionalFields' => CleanupAdditionalFieldProvider::class
];