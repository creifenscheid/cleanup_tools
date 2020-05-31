<?php

defined('TYPO3_MODE') or die();

// Register icons
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Imaging\IconRegistry::class);

$icons = [
    'tx-cleanuptools-icon' => 'EXT:cleanup_tools/Resources/Public/Icons/tx_cleanuptools_icon.svg',
    'tx-cleanuptools-restore' => 'EXT:cleanup_tools/Resources/Public/Icons/tx_cleanuptools_restore.svg'
];

foreach ($icons as $iconKey => $pathToIcon) {
    $iconRegistry->registerIcon($iconKey, TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => $pathToIcon
    ]);
}

// get extension configuration
$extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('cleanup_tools');

// Register toolbar item
if ($extensionConfiguration['enableToolbarItem']) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433112] = \ChristianReifenscheid\CleanupTools\Backend\Toolbar\CleanupToolbarItem::class;
}

// HOOK: After database operations hook
if ($extensionConfiguration['enableAfterDatabaseOperationsHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['cleanup_tools'] = \ChristianReifenscheid\CleanupTools\Hooks\AfterDatabaseOperationsHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['cleanup_tools'] = \ChristianReifenscheid\CleanupTools\Hooks\AfterDatabaseOperationsHook::class;
}

// HOOK: DrawItem
if ($extensionConfiguration['enablePreviewRenderer']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] = \ChristianReifenscheid\CleanupTools\Hooks\PreviewRenderer::class;
}

// TASK
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\ChristianReifenscheid\CleanupTools\Task\CleanupTask::class] = [
    'extension' => 'cleanup_tools',
    'title' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.title',
    'description' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.description',
    'additionalFields' => \ChristianReifenscheid\CleanupTools\Task\CleanupAdditionalFieldProvider::class
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\ChristianReifenscheid\CleanupTools\Task\HistoryTask::class] = [
    'extension' => 'cleanup_tools',
    'title' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.history.title',
    'description' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.history.description',
    'additionalFields' => \ChristianReifenscheid\CleanupTools\Task\HistoryAdditionalFieldProvider::class
];