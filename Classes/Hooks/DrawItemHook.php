<?php
namespace SPL\SplCleanupTools\Hooks;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

/**
 * Class NewContentElementPreviewRenderer
 *
 * @package SPL\SplCleanupTools\Hooks
 * @author Christian Reifenscheid
 */
class DrawItemHook implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface
{
    
    /**
     * Adjust the preview rendering of a elements with flexforms
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
     * @param bool $drawItem Whether to draw the item using the default functionality
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     *
     * @return void
     */
    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
        
        // Admins only
        if ($GLOBALS['BE_USER']->isAdmin()) {
            
            // check if field:pi_flexform is set
            if ($row['pi_flexform']) {
                
                /* @var \SPL\SplCleanupTools\Utility\FlexFormUtility $flexFormUtility */
                $flexFormUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\FlexFormUtility::class);
                
                if (!$flexFormUtility->isFlexFormClean((int) $row['uid'])) {
                   
                    /** @var \TYPO3\CMS\Core\Imaging\IconFactory */
                    $iconFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
                    $iconTitle = 'This element has translations on wrong page id!';
                    $icon = $iconFactory->getIcon('tx-splcleanuptools-toolbar', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
                    $iconMarkup = $icon->render();
                    $originalHeaderContent = $headerContent;
                    
                    $bla = '
                        <div class="btn-toolbar" role="toolbar" aria-label="">
				            <a href="/typo3/index.php?route=%2Fweb%2Flayout%2F&amp;token=58b06528c6a4b0bc0422006e677b2eba50b4b576&amp;id=1&amp;clear_cache=1" class="btn btn-default btn-sm " title="Clear cache for this page">
'.$iconMarkup.'
                            </a>
                        </div>
';
                    
                    $headerContent = '<div style="position: absolute; right: 10px; width: 16px; height: 16px;" title="' . $iconTitle . '">' . $bla . '</div>' . $originalHeaderContent;
                }
            }
        }
    }
}