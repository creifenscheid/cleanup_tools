<?php
use ChristianReifenscheid\CleanupTools\Backend\Toolbar\CleanupToolbarItem;
use ChristianReifenscheid\CleanupTools\Hooks\AfterDatabaseOperationsHook;
use ChristianReifenscheid\CleanupTools\Hooks\DrawItemHook;
use ChristianReifenscheid\CleanupTools\Task\CleanupAdditionalFieldProvider;
use ChristianReifenscheid\CleanupTools\Task\CleanupTask;

defined('TYPO3_MODE') or die();

// Register icons
$iconRegistry = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Imaging\IconRegistry::class);

$icons = [
    'tx-cleanuptools-icon' => 'EXT:cleanup_tools/Resources/Public/Icons/tx_cleanuptools_icon.svg',
    tx-cleanuptools-restore => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_cleanuptools_restore.svg'
];

foreach ($icons as $iconKey => $pathToIcon) {
    $iconRegistry->registerIcon($iconKey, TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => $pathToIcon
    ]);
}

// get extension configuration
$extensionConfiguration = GeneralUtility::makeInstance(TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('cleanup_tools');

// Register toolbar item
if ($extensionConfiguration['enableToolbarItem']) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433112] = CleanupToolbarItem::class;
}

// HOOK: After database operations hook
if ($extensionConfiguration['enableAfterDatabaseOperationsHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['cleanup_tools'] = AfterDatabaseOperationsHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['cleanup_tools'] = AfterDatabaseOperationsHook::class;
}

// HOOK: DrawItem
if ($extensionConfiguration['enableDrawItemHook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] = DrawItemHook::class;
}

// TASK
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][CleanupTask::class] = [
    'extension' => 'cleanup_tools',
    'title' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.title',
    'description' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.description',
    'additionalFields' => CleanupAdditionalFieldProvider::class
];