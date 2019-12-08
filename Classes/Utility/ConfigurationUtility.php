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
 * Class ConfigurationUtility
 *
 * @package SPL\SplCleanupTools\Utility
 * @author  Christian Reifenscheid
 */
class ConfigurationUtility
{

    /**
     * Module configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Configured utilities incl.
     * existing and allowed methods
     *
     * @var array
     */
    protected $utilities = [];
    
    /**
     * Configured additional usages of utilities incl.
     * existing and allowed methods
     *
     * @var array
     */
    protected $additionalUsages = [];
    

    /**
     * Constructor
     */
    public function __construct()
    {
        // init object manager
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        // init configurationManager
        $configurationManager = $objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        // init typoscript service
        $typoscriptService = $objectManager->get(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class);

        // get module configuration
        $this->configuration = $typoscriptService->convertTypoScriptArrayToPlainArray($extbaseFrameworkConfiguration['module.']['tx_splcleanuptools.']);

        // loop through configured utilities
        foreach ($this->configuration['utilities'] as $utilityClass => $utilityConfiguration) {

            // set utility information
            $this->utilities[$utilityClass] = [
                'name' => end(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('\\', $utilityClass)),
                'class' => $utilityClass
            ];

            if ($utilityConfiguration['color']) {
                $this->utilities[$utilityClass]['color'] = $utilityConfiguration['color'];
            }

            // get and store class methods
            $methods = get_class_methods(new $utilityClass());

            // loop through every method
            foreach ($methods as $method) {

                // check method
                if ($this->checkBlacklist($method, $utilityConfiguration['methods'])) {

                    $reflection = new \ReflectionMethod($utilityClass, $method);
                    $parameters = $reflection->getParameters();

                    $methodParameters = [];

                    foreach ($parameters as $parameter) {

                        $methodParameters[] = [
                            'name' => $parameter->getName(),
                            'label' => $this->unLowerCamelCase($parameter->getName()),
                            'formType' => $this->configuration['mapping']['parameter'][$parameter->getName()]
                        ];
                    }
                    
                    // prepare method information
                    $methodInformation = [
                        'name' => $this->unLowerCamelCase($method),
                        'method' => $method,
                        'parameters' => $methodParameters,
                        'parameterConfiguration' => $utilityConfiguration['methods']['parameterConfigurations'][$method] ?: null
                    ];

                    // add method information to storage
                    $this->utilities[$utilityClass]['methods'][] = $methodInformation;
                    
                    // check additional usage configuration of utility
                    foreach ($utilityConfiguration['additionalUsage'] as $additionalUsageType => $additionalUsageConfiguration) {
                        if ((int)$additionalUsageConfiguration['enable'] === 1) {
                            
                            // check if method is blacklisted for additional usage
                            if ($this->checkBlacklist($method, $additionalUsageConfiguration)) { 
                                $this->additionalUsages[$additionalUsageType][$utilityClass][] = $methodInformation;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns utilities incl.
     * methods and configuration
     *
     * @return array
     */
    public function getAllUtilities() : array
    {
        return $this->utilities;
    }
    
    /**
     * Return utilities for an additional usage
     * 
     * @param string $usageType
     * @return array|NULL
     */
    public function getUtilitiesByAdditionalUsage (string $usageType) : ?array
    {
        if ($this->additionalUsages[$usageType]) {
            return $this->additionalUsages[$usageType];
        }
            
        return null;
    }

    /**
     * Return utility of given method
     *
     * @param string $methodName
     *
     * @return array|NULL
     */
    public function getUtilityByMethod(string $methodName) : ?array
    {
        foreach ($this->utilities as $utility) {
            foreach ($utility['methods'] as $method) {
                if ($method['method'] === $methodName) {
                    return $utility;
                }
            }
        }

        return null;
    }
    
    /**
     * Get configuration of method
     *
     * @param string $methodName
     *
     * @return array|null
     */
    public function getMethodConfiguration(string $methodName) : ?array
    {
        foreach ($this->utilities as $utility) {
            foreach ($utility['methods'] as $method) {
                if ($method['method'] === $methodName) {
                    return $method;
                }
            }
        }
        
        return null;
    }

    /**
     * Function to check if a method of a utility is blacklisted
     *
     * @param string $method
     * @param array  $configuration
     *
     * @return bool
     */
    private function checkBlacklist($method, $configuration) : bool
    {

        // get configured includes and excludes
        $excludes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['excludes']);

        // if method is in excludes - return false to skip the method
        if (in_array($method, $excludes)) {
            return false;
        }

        // if method is not in the configuration - return true to add the method
        return true;
    }

    /**
     * Function to transform strings from lowerCamelCase to string with spaces
     *
     * @param string $input
     *
     * @return string
     */
    private function unLowerCamelCase(string $input) : string
    {

        // 1. turn lowerCamelCase method name into lower case underscored
        // 2. replace underscores by space
        // 3. set the first char to upper case
        return ucfirst(str_replace('_', ' ', \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($input)));
    }
}
