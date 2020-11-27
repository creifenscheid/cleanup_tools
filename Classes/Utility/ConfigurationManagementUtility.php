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
     * ConfigurationManagementUtility constructor
     */
    public function __construct()
    {
        // check if configuration array exists
        if(!$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools']['cleanup_services']) {
            // otherwise create it
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools']['cleanup_services'] = [];
        }
    }

    /**
     * Register cleanup service
     * 
     * @param string $identifier
     * @param string $className
     * @param bool $schedulerTask
     * @param bool $toolbar
     * @param bool $enabled
     *
     * @return void
     */
    public static function addCleanupService (string $identifier, string $className, bool $schedulerTask = true, bool $toolbar = true, bool $enabled = true) : void
    {
        $configurationArray = self::getConfiguration();
       
        $configurationArray[$identifier] = [
            'class' => $className,
            'schedulerTask' => $enabledInSchedulerTask,
            'toolbar' => $enabledInToolbar,
            'enabled' => $enabled
        ];
        
        self::writeConfiguration($configurationArray);
    }
    
    /**
     * Set property of cleanup service
     * 
     * @param string $identifier
     * @param string $property
     * @param string|bool $value
     *
     * @return void
     */
    private static function setProperty(string $identifier, string $property, $value) : void
    {
        $configurationArray = self::getConfiguration();
       
        if ($configurationArray[$identifier] && $configurationArray[$identifier][$property]) {
            $configurationArray[$identifier][$property] =  $value;
            
            self::writeConfiguration($configurationArray);
        } else if (!$configurationArray[$identifier]) {
            // @todo throw identifier error
        } else {
            // @todo throw property error 
        }
    }
    
    /**
     * Enable/disable cleanup service
     * 
     * @param string $identifier
     * @param bool $value
     *
     * @return void
     */
    public static function setEnable (string $identifier, bool $value) : void
    {
        self::setProperty($identifier, 'enabled', $value);
    }
    
    /**
     * Returns configuration array within $GLOBALS
     * 
     * @return array
     */
    private static function getConfiguration() : array 
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools']['cleanup_services'];
    }
    
    /**
     * Writes configuration array
     * 
     * @param array $configurationArray
     */
    private static function writeConfiguration(array $configurationArray) : void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleanup_tools']['cleanup_services'] = $configurationArray;
    }
}
