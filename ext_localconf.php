<?php

defined ('TYPO3_MODE') or die();

// Register toolbar item
$GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433112] = \SPL\SplCleanupTools\Backend\Toolbar\CleanUpToolbarItem::class;

// Register icons
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
);
$iconRegistry->registerIcon(
    'tx-splcleanuptools-toolbar',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_toolbar.svg']
);