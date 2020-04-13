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
     * Functions
     */
    const FUNCTION_MAIN = 'execute';

    /**
     * @var \SPL\SplCleanupTools\Domain\Repository\LogRepository
     */
    protected $logRepository;

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
     * Services which not provide function "execute"
     *
     * @var array
     */
    protected $errorServices = [];

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
        $this->logRepository = $objectManager->get(LogRepository::class);

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
            if (method_exists($serviceClass, self::FUNCTION_MAIN)) {

                // set up service configuration
                $this->services[$serviceClass] = $this->prepareClassConfiguration($serviceClass, self::FUNCTION_MAIN, $serviceConfiguration);
            } else {

                $this->errorServices[] = $serviceClass;
            }
            

            // check additional usage configuration of service
            foreach ($serviceConfiguration['additionalUsage'] as $additionalUsageType => $state) {
                if ((int)$state === 1) {
                    $this->additionalUsages[$additionalUsageType][$serviceClass] = $this->prepareClassConfiguration($serviceClass, 'execute', $serviceConfiguration);
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
     * Returns all services incl. configuration
     *
     * @return array
     */
    public function getServices() : array
    {
        return $this->services;
    }

    /**
     * Returns single service incl. configuration
     *
     * @param string $class
     *
     * @return array
     */
    public function getService($class) : array
    {
        return $this->services[$class];
    }

    /**
     * Returns failed services
     *
     * @return array
     */
    public function getErrorServices() : array
    {
        return $this->errorServices;
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
     * Function to prepare class configuration
     *
     * @param string $class
     * @param string $method
     * @param array  $configuration
     *
     * @return array
     * @throws \ReflectionException
     */
    private function prepareClassConfiguration(string $class, string $method, array $configuration) : array
    {
        // init reflection of class
        $reflection = new \ReflectionClass($class);

        $name = str_replace('Service', '', end(GeneralUtility::trimExplode('\\', $class)));

        $methodParameters = [];

        foreach ($reflection->getDefaultProperties() as $parameterName => $defaultValue) {
            $parameterConfiguraton = [
                'name' => $parameterName,
                'mandatory' => ($defaultValue === null)
            ];
            
            if (\gettype($defaultValue) && \gettype($defaultValue) !== 'NULL') {
                $parameterConfiguraton['type'] = \gettype($defaultValue);
            } else if ($configuration['mapping']['parameter'][$parameterName]) {
                $parameterConfiguraton['type'] = $configuration['mapping']['parameter'][$parameterName];
            }

            if ($defaultValue !== null) {
                $parameterConfiguraton['default'] = $defaultValue;
            }

            $methodParameters[$parameterName] = $parameterConfiguraton;
        }

        // prepare method information
        $classConfiguration = [
            'name' => $name,
            'class' => $class,
            'method' => [
                'name' => $method,
                'parameters' => $methodParameters
            ]
        ];

        // get last log of method
        /** @var \SPL\SplCleanupTools\Domain\Model\Log $lastLog */
        $lastLog = $this->logRepository->findByService($class);

        if ($lastLog) {
            $classConfiguration['daysSince'] = round((time() - $lastLog->getCrdate()) / 60 / 60 / 24);
            $classConfiguration['lastLog'] = $lastLog;
        }

        return $classConfiguration;
    }
}
