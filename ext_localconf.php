<?php

defined ('TYPO3_MODE') or die();

// Register icons
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );

$iconRegistry->registerIcon(
    'tx-splcleanuptools-icon',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_icon.svg']
    );

// get extension configuration
$backendConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
->get('spl_cleanup_tools');

// Register toolbar item
if ($backendConfiguration['enableToolbarItem']) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433112] = \SPL\SplCleanupTools\Backend\Toolbar\CleanUpToolbarItem::class;
}

// HOOK: After database operations hook
if ($backendConfiguration['enableAfterDatabaseOperationsHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['spl_cleanup_tools'] = \SPL\SplCleanupTools\Hooks\AfterDatabaseOperationsHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['spl_cleanup_tools'] = \SPL\SplCleanupTools\Hooks\AfterDatabaseOperationsHook::class;
}

// HOOK: DrawItem
if ($backendConfiguration['enableDrawItemHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] = \SPL\SplCleanupTools\Hooks\DrawItemHook::class;
}

// TASK
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\SPL\SplCleanupTools\Task\CleanupTask::class] = [
    'extension' => 'spl_cleanup_tools',
    'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.title',
    'description' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.description',
    'additionalFields' => \SPL\SplCleanupTools\Task\CleanupAdditionalFieldProvider::class
];