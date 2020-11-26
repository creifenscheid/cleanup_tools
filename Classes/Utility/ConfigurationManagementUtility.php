<?php

namespace CReifenscheid\CleanupTools\Utility;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 C. Reifenscheid
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
 * Class ConfigurationManagementUtility
 *
 * @package CReifenscheid\CleanupTools\Utility
 * @author  C. Reifenscheid
 */
class ConfigurationManagementUtility
{
    /**
     * Register cleanup service
     * 
     * @param string $identifier
     * @param string $className
     * @param bool $availableInTask
     * @param bool $availableInToolbar
     * @param bool $enabled
     */
    public static function addCleanupService (string $identifier, string $className, bool $availableInTask = true, bool $availableInToolbar = true, bool $enabled = true) : void
    {
        $configurationArray = self::getConfiguration();
       
        $configurationArray[$identifier] = [
            'class' => $className
        ];
        
        self::writeConfiguration($configurationArray);
    }
    
    /**
     * Returns configuration array within $GLOBALS
     * 
     * @return array
     */
    private static function getConfiguration() : array 
    {
        // check if configuration array already exists
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools']['cleanup_services']) {
            return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools']['cleanup_services'];
        }
        
        // create configuration array
        $configurationArray = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools'];
        $configurationArray['cleanup_services'] = [];
        
        return $configurationArray;
    }
    
    /**
     * Writes configuration array
     * 
     * @param array $configurationArray
     */
    private static function writeConfiguration(array $configurationArray) : void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools'] = $configurationArray;
    }
}
