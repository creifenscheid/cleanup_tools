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
     * @var \SPL\SplCleanupTools\Utility\ConfigurationUtility
     */
    protected $configurationUtility;
    
    /**
     *
     * @var \SPL\SplCleanupTools\Utility\CleanupUtility
     */
    protected $cleanupUtility;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->configurationUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\ConfigurationUtility::class);
        $this->cleanupUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\CleanupUtility::class);
    }

    /**
     * action index
     *
     * @return void
     */
    public function indexAction(): void
    {
        // assign utilities to the view
        $this->view->assign('utilities', $this->configurationUtility->getAllUtilities());
    }

    /**
     * action cleanup
     *
     * @return void
     * @throws \ReflectionException
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

            $result = $this->cleanupUtility->processAction($utilityActionName,$utilityActionParameter);
            
            if ($result) {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.message'),
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.headline'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                );
            }
            
            else {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message'),
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                    );
            }
            
            $this->forward('index', 'Cleanup','SplCleanupTools');
        }
    }
}
