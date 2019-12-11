<?php
namespace SPL\SplCleanupTools\Controller;

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
 * Class CleanupController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author Christian Reifenscheid
 */
class CleanupController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * 
     * @var \SPL\SplCleanupTools\Service\ConfigurationService
     */
    protected $configurationService;
    
    /**
     *
     * @var \SPL\SplCleanupTools\Service\CleanupService
     */
    protected $cleanupService;
    
    /**
     * Localization file
     *
     * @var string
     */
    protected $localizationFile = '';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Service\ConfigurationService::class);
        $this->cleanupService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Service\CleanupService::class);
        $this->localizationFile = $this->configurationService->getLocalizationFile();
    }

    /**
     * action index
     *
     * @return void
     */
    public function indexAction(): void
    {
        // assign utilities to the view
        $this->view->assignMultiple([
            'utilities' => $this->configurationService->getAllUtilities(),
            'localizationFile' => $this->configurationService->getLocalizationFile()
        ]);
    }

    /**
     * action cleanup
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function cleanupAction(): void
    {
        // get arguments from request
        $arguments = $this->request->getArguments();

        // check for required arguments
        if ($arguments['utilityAction']) {

            // get utility and utility action from arguments
            $utilityActionName = $arguments['utilityAction'];
            $utilityActionParameter = $arguments['parameters'];

            $result = $this->cleanupService->processAction($utilityActionName,$utilityActionParameter);
            
            if ($result) {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->localizationFile.':messages.success.message','SplCleanupTools',[$utilityActionName]),
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->localizationFile.':messages.success.headline','SplCleanupTools'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                );
            }
            
            else {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->localizationFile.':messages.error.message','SplCleanupTools',[$utilityActionName]),
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->localizationFile.':messages.error.headline','SplCleanupTools'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                    );
            }
            
            $this->forward('index', 'Cleanup','SplCleanupTools');
        }
    }
}
