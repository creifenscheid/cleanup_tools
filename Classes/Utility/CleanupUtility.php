<?php

namespace SPL\SplCleanupTools\Utility;

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
 * Class CleanupUtility
 *
 * @package SPL\SplCleanupTools\Utility
 * @author  Christian Reifenscheid
 */
class CleanupUtility
{
    /**
     * Configuration utility
     * 
     * @var \SPL\SplCleanupTools\Utility\ConfigurationUtility
     */
    protected $configurationUtility;
    
    /**
     * Object manager
     * 
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;
    
    /**
     * Constructor
     */
    public function __construct() {
        // init object manager
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        // init configuration utility
        $this->configurationUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\ConfigurationUtility::class);
    }

    /**
     * Function to initialze a utility and call the requested action
     *
     * @param string $action
     * @param array  $parameters
     *
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function processAction(string $action, array $parameters = null) : bool {
        // define return var
        $return = false;

        // get utility of cleanCmd
        $utility = $this->configurationUtility->getUtilityByMethod($action);
        
        // if a utility is returned
        if ($utility) {
            
            // get utility class
            $utilityClass = $utility['class'];
            
            // init utility
            $utility = $this->objectManager->get($utilityClass);
            
            // if parameter are given
            if ($parameters) {
                // call action with parameter
                $return = \call_user_func_array([$utility,$action], $parameters);
            } else {

                // call action
                $return = $utility->$action();
            }
        }
        
        return $return;
        
    }
}
