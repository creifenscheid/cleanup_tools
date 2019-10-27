<?php
namespace SPL\SplCleanupTools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\AbstractController;

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
    
    public function __construct() {
        $this->configurationUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\ConfigurationUtility::class);
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
        if ($arguments['utilityAction'] && $arguments['utilityClass']) {

            // get utility and utility action from arguments
            $utilityClass = $arguments['utilityClass'];
            $utilityActionName = $arguments['utilityAction'];
            $utilityActionParameter = $arguments['parameters'];

            // init utility
            $utility = $this->objectManager->get($utilityClass);

            // call action in utility
            if ($utilityActionParameter) {
                $result = call_user_func_array([$utility,$utilityActionName], $utilityActionParameter);
            } else {
                $result = $utility->$utilityActionName();
            }

            $this->view->assignMultiple([
                'result' => $result,
                'service' => $utilityClass
            ]);
        }
    }
    
    /**
     * Toolbar action
     *
     *
     */
    public function toolbarAction ()
    {
        // init object manager
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        // get request
        /** @deprecated in TYPO3 9 - will be removed in TYPO3 10 */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $queryParams = $request->getQueryParams();
        
        $clearCmd = $queryParams['clearCmd'] ? : null;
        
        // if clearCmd is given
        if ($clearCmd) {
            
            // get utility of clearCmd
            $utility = $this->configurationUtility->getUtilityByMethod($clearCmd);
            
            // if a utility is returned
            if ($utility) {
                
                // get utility class
                $utilityClass = $utility['class'];
                
                // init utility
                $utility = $objectManager->get($utilityClass);
                $result = $utility->$clearCmd(); 
            }
        }
        
        return new \TYPO3\CMS\Core\Http\HtmlResponse('');
    }
}
