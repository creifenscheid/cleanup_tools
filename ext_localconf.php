<?php

defined ('TYPO3_MODE') or die();

// Register icons
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
);
$iconRegistry->registerIcon(
    'tx-splcleanuptools-toolbar',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_toolbar.svg']
);

// get extension configuration
$backendConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
->get('spl_cleanup_tools');

// Register toolbar item
if ($backendConfiguration['enableToolbarItem']) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433112] = \SPL\SplCleanupTools\Backend\Toolbar\CleanUpToolbarItem::class;
}

// TASK: JOBS
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\SPL\SplCleanupTools\Task\CleanupTask::class] = [
    'extension' => 'spl_cleanup_tools',
    'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.title',
    'description' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.description',
    'additionalFields' => \SPL\SplCleanupTools\Task\CleanupAdditionalFieldProvider::class
];