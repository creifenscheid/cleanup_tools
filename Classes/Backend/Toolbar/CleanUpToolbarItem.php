<?php
namespace SPL\SplCleanupTools\Backend\Toolbar;

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
 * Class CleanUpToolbarItem
 *
 * @package SPL\SplCleanupTools\Backend\Toolbar
 * @author Christian Reifenscheid
 */
class CleanUpToolbarItem implements \TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface
{
    /**
     * @var array
     */
    protected $cleanupActions = [];
    
    /**
     * @var string
     */
    protected $localizationFile = '';

    /**
     * CleanUpToolbarItem constructor.
     *
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function __construct() {
        
        /** @var \SPL\SplCleanupTools\Service\ConfigurationService $configurationService */
        $configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Service\ConfigurationService::class);
        
        $this->localizationFile = $configurationService->getLocalizationFile();

        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder **/
        $uriBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);

        $toolbarItems = $configurationService->getUtilitiesByAdditionalUsage('toolbar');
        
        foreach ($toolbarItems as $utility => $methods) {
            
            foreach ($methods as $method) {
                
                $uri = (string)$uriBuilder->buildUriFromRoute('splcleanuptools_ajax', ['action' => $method['method']]);
                
                $this->cleanupActions[] = [
                    'title' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->localizationFile.':label.'.$method['method']),
                    'description' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->localizationFile.':description.'.$method['method']),
                    'utility' => $utility,
                    'onclickCode' => 'TYPO3.SplCleanupToolsActions.process(' . \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue($uri) . '); return false;'
                ];
            }
        }
    }

    /**
     * Checks whether the user has access to this toolbar item
     *
     * @return bool TRUE if user has access, FALSE if not
     */
    public function checkAccess() {
        $backendUser = $this->getBackendUser();
        if ($backendUser->isAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * Render toolbar item
     *
     * @return string Toolbar item HTML
     */
    public function getItem() {
        $view = $this->getFluidTemplateObject('CleanUpToolbarItem.html');
        $view->assign('localizationFile', $this->localizationFile);
        return $view->render();
    }

    /**
     * Toolbar has a drop down
     *
     * @return bool
     */
    public function hasDropDown() {
        return true;
    }

    /**
     * Render "drop down" part of this toolbar
     *
     * @return string Drop down HTML
     */
    public function getDropDown() {
        $view = $this->getFluidTemplateObject('CleanUpToolbarItemDropDown.html');
        $view->assignMultiple([
            'cleanupActions' => $this->cleanupActions,
            'localizationFile' => $this->localizationFile
        ]);
        return $view->render();
    }

    /**
     * No additional attributes needed
     *
     * @return array
     */
    public function getAdditionalAttributes() {
        return [];
    }

    /**
     * Return index
     *
     * @return int 0 .. 100
     */
    public function getIndex() {
        return 50;
    }

    /**
     * Returns a new standalone view, shorthand function
     *
     * @param string $filename Which templateFile should be used.
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected function getFluidTemplateObject(string $filename): \TYPO3\CMS\Fluid\View\StandaloneView
    {
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setLayoutRootPaths(['EXT:spl_cleanup_tools/Resources/Private/Backend/ToolbarItems/Layouts']);
        $view->setPartialRootPaths(['EXT:spl_cleanup_tools/Resources/Private/Backend/ToolbarItems/Partials']);
        $view->setTemplateRootPaths(['EXT:spl_cleanup_tools/Resources/Private/Backend/ToolbarItems/Templates']);
        $view->setTemplate($filename);

        $view->getRequest()->setControllerExtensionName('Backend');
        return $view;
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
