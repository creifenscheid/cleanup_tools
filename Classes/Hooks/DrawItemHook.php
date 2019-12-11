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
                
                if (!$flexFormUtility->isValidFlexForm((int) $row['uid'])) {
                    
                    /** @var \SPL\SplCleanupTools\Service\ConfigurationService $configurationService */
                    $configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Service\ConfigurationService::class);
                    
                    /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder **/
                    $uriBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
                   
                    /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
                    $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
                    
                    // set format
                    $view->setFormat('html');
                    
                    $uri = (string)$uriBuilder->buildUriFromRoute('splcleanuptools_ajax', ['action' => 'cleanupFlexForms']);
                    
                    // assignments
                    $view->assignMultiple([
                        'onClickCode' => 'TYPO3.SplCleanupToolsActions.process(' . \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue($uri) . ','.$row['uid'].'); return false;',
                        'recordUid' => $row['uid'],
                        'localizationFile' => $configurationService->getLocalizationFile()
                    ]);
                    
                    // set template path
                    $templateFile = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath(PATH_site . 'typo3conf/ext/spl_cleanup_tools/Resources/Private/Backend/Templates/DrawItemHook/Index.html');
                    
                    // set view template
                    $view->setTemplatePathAndFilename($templateFile);
                    
                    $headerContent .= $view->render();
                }
            }
        }
    }
}