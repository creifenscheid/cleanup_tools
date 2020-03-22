<?php

namespace SPL\SplCleanupTools\Service;

use ReflectionMethod;
use SPL\SplCleanupTools\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use function in_array;

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
 * Class ConfigurationService
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class ConfigurationService implements SingletonInterface
{

    /**
     * Module configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Configured services incl.
     * method information
     *
     * @var array
     */
    protected $services = [];
    
    /**
     * Services which can be performed for single elements, e.g. in hook context
     * method information 
     *
     * @var array
     */
    protected $singleServices = [];

    /**
     * Configured additional usages of utilities incl.
     * existing and allowed methods
     *
     * @var array
     */
    protected $additionalUsages = [];

    /**
     * localizationFile
     *
     * @var string
     */
    protected $localizationFile = '';


    /**
     * Constructor
     */
    public function __construct()
    {
        // init object manager
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // init configurationManager
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        // init typoscript service
        $typoscriptService = $objectManager->get(TypoScriptService::class);

        // init log repository
        $logRepository = $objectManager->get(LogRepository::class);

        // get module configuration
        $this->configuration = $typoscriptService->convertTypoScriptArrayToPlainArray($extbaseFrameworkConfiguration['module.']['tx_splcleanuptools.']);

        // set localization from ts configuration
        $this->localizationFile = $this->configuration['settings']['localizationFile'] ? : 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_services.xlf';

        // loop through configured utilities
        foreach ($this->configuration['services'] as $serviceClass => $serviceConfiguration) {

            // skip service if not enabled
            if (!$serviceConfiguration['enable']) {
                continue;
            }

            // check if execute() exists
            if (method_exists($serviceClass, 'execute') {
                // set up service configuration
                $this->services[$serviceClass]['execute'] = $this->prepareMethodInformation($serviceClass, 'execute');
            
            // check if executeSingle() exists
            if (method_exists($serviceClass, 'executeSingle') {
            
                // set up service configuration
                $this->singleServices[$serviceClass]['executeSingle']= $this->prepareMethodInformation($serviceClass, 'executeSingle');
            }
                    // get last log of method
                    /** @var \SPL\SplCleanupTools\Domain\Model\Log $lastLog */
                    $lastLog = $logRepository->findByServiceAndMethod($serviceClass, $method);

                    if ($lastLog) {
                        $methodInformation['daysSince'] = round((time() - $lastLog->getCrdate()) / 60 / 60 / 24);
                        $methodInformation['lastLog'] = $lastLog;
                    }

                    // check additional usage configuration of utility
                    foreach ($serviceConfiguration['additionalUsage'] as $additionalUsageType => $additionalUsageConfiguration) {
                        if ((int)$additionalUsageConfiguration['enable'] === 1) {

                            // check if method is blacklisted for additional usage
                            if ($this->checkBlacklist($method, $additionalUsageConfiguration)) {
                                $this->additionalUsages[$additionalUsageType][$serviceClass][$method] = $methodInformation;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the localization file
     *
     * @return string
     */
    public function getLocalizationFile() : string
    {
        return $this->localizationFile;
    }

    /**
     * Returns services incl.
     * methods and configuration
     *
     * @return array
     */
    public function getAllServices() : array
    {
        return $this->services;
    }

    /**
     * Returns single services incl.
     * methods and configuration
     *
     * @return array
     */
    public function getAllSingleServices() : array
    {
        return $this->singleServices;
    }
    
    /**
     * Return services for an additional usage
     *
     * @param string $usageType
     *
     * @return array|NULL
     */
    public function getServicesByAdditionalUsage(string $usageType) : ?array
    {
        if ($this->additionalUsages[$usageType]) {
            return $this->additionalUsages[$usageType];
        }

        return null;
    }

    /**
     * Function to prepare method information of a class
     *
     * @param string $class
     * @param string $method
     *
     * @return array
     */
    private function prepareMethodInformation (string $class, string $method) : array
    {
        $methodInformation = [
            'name' => end(GeneralUtility::trimExplode('\\', $serviceClass)),
            'class' => $serviceClass
        ];
                
        // init reflection of method
        $reflection = new ReflectionMethod($serviceClass, $method);
        
        // check method
        if ($reflection->isPublic() && $this->checkBlacklist($method, $serviceConfiguration['methods'])) {
            
            $methodParameters = [];

            foreach ($reflection->getParameters() as $parameter) {
                $methodParameters[] = [
                    'name' => $parameter->getName(),
                    'type' => $parameter->getType() ? ucfirst($parameter->getType()->getName()) : ucfirst($this->configuration['mapping']['parameter'][$parameter->getName()]),
                    'mandatory' => $parameter->isdefaultvalueavailable() ? false : true
                ];
            }

            // prepare method information
            $methodInformation = [
                'method' => $method,
                'parameters' => $methodParameters,
                        'parameterConfiguration' => $serviceConfiguration['methods']['parameterConfigurations'][$method] ? : null
            ];
        }
        
        return $methodInformation;
    }
}
