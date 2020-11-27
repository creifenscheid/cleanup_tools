<?php
namespace CReifenscheid\CleanupTools\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class ConfigurationService
 *
 * @package CReifenscheid\CleanupTools\Service
 * @author C. Reifenscheid
 */
class ConfigurationService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Functions
     */
    const FUNCTION_MAIN = 'execute';
    
    /**
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;
    
    /**
     *
     * @var \CReifenscheid\CleanupTools\Domain\Repository\LogRepository
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
     * Configured additional usages of services
     *
     * @var array
     */
    protected $additionalUsages = [];

    /**
     * localizationFilePaths
     *
     * @var array
     */
    protected $localizationFilePaths = [];

    /**
     * log lifetime options
     *
     * @var array
     */
    protected $logLifetimeOptions = [];

    /**
     * Constructor
     *
     * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     * @param \TYPO3\CMS\Core\TypoScript\TypoScriptService $typoScriptService
     * @param \CReifenscheid\CleanupTools\Domain\Repository\LogRepository $logRepository
     */
    public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager,\TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager, \TYPO3\CMS\Core\TypoScript\TypoScriptService $typoScriptService, \CReifenscheid\CleanupTools\Domain\Repository\LogRepository $logRepository)
    {   
        // get extension configuration
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('cleanup_tools');
        
        // log lifetime options
        $logLifetimeOptions = $extensionConfiguration['logLifetimeOptions'] ? \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $extensionConfiguration['logLifetimeOptions']) : [];
        
        if ($logLifetimeOptions) {
            foreach ($logLifetimeOptions as $logLifetimeOption) {
                $this->logLifetimeOptions[str_replace(' ', '-', $logLifetimeOption)] = $logLifetimeOption;
            }
        }
        
        // get localization file paths from typoscript configuration
        $this->localizationFilePaths = $extensionConfiguration['localizationFilePaths'];
                
        
        ########
        ### OLD        
        
        
        
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        // set object manager
        $this->objectManager = $objectManager;
        
        // set log repository
        $this->logRepository = $logRepository;

        // get module configuration
        $moduleConfiguration = $extbaseFrameworkConfiguration['module.']['tx_cleanuptools.'];
        
        if ($moduleConfiguration) {
            $this->configuration = $typoScriptService->convertTypoScriptArrayToPlainArray($moduleConfiguration);
        }

        if ($this->configuration) {
            // loop through configured services
            if ($this->configuration['services']) {
                foreach ($this->configuration['services'] as $serviceClass => $serviceConfiguration) {

                    // skip service if not enabled
                    if (! $serviceConfiguration['enable']) {
                        continue;
                    }

                    // check if execute() exists
                    if (method_exists($serviceClass, self::FUNCTION_MAIN)) {

                        // set up service configuration
                        $this->services[$serviceClass] = $this->prepareClassConfiguration($serviceClass, self::FUNCTION_MAIN, $serviceConfiguration);

                        // check additional usage configuration of service
                        foreach ($serviceConfiguration['additionalUsage'] as $additionalUsageType => $state) {
                            if ((int) $state === 1) {
                                $this->additionalUsages[$additionalUsageType][$serviceClass] = $this->prepareClassConfiguration($serviceClass, 'execute', $serviceConfiguration);
                            }
                         }
                    } else {
                        $this->errorServices[] = $serviceClass;
                    }
                }
            }
        }
    }
    
    /**
     * Returns the localization file paths
     *
     * @return array
     */
    public function getLocalizationFilePaths(): array
    {
        $localizationPaths = $this->localizationFilePaths;
        krsort($localizationPaths);
        return $localizationPaths;
    }

    /**
     * Returns log lifetime options
     *
     * @return array
     */
    public function getLogLifetimeOptions(): array
    {
        return $this->logLifetimeOptions;
    }

    /**
     * Returns all services incl.
     * configuration
     *
     * @return array
     */
    public function getServices(): array
    {
        $services = $this->services;
        ksort($services);
        return $services;
    }

    /**
     * Returns single service incl.
     * configuration
     *
     * @param string $class
     *
     * @return array
     */
    public function getService($class): array
    {
        return $this->services[$class];
    }

    /**
     * Returns failed services
     *
     * @return array
     */
    public function getErrorServices(): array
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
    public function getServicesByAdditionalUsage(string $usageType): ?array
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
     * @param array $configuration
     * @return array
     */
    private function prepareClassConfiguration(string $class, string $method, array $configuration): array
    {
        // init reflection of class
        $reflection = $this->objectManager->get(\ReflectionClass::class, $class);

        $name = end(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('\\', $class));

        $methodParameters = [];

        foreach ($reflection->getDefaultProperties() as $parameterName => $defaultValue) {
            $parameterConfiguraton = [
                'name' => $parameterName,
                'mandatory' => ($defaultValue === null)
            ];

            if (\gettype($defaultValue) && \gettype($defaultValue) !== 'NULL') {
                $parameterConfiguraton['type'] = ucfirst(\gettype($defaultValue));
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
        /** @var \CReifenscheid\CleanupTools\Domain\Model\Log $lastLog */
        $lastLog = $this->logRepository->findByService($class);

        if ($lastLog) {
            $classConfiguration['daysSince'] = round((time() - $lastLog->getCrdate()) / 60 / 60 / 24);
            $classConfiguration['lastLog'] = $lastLog;
        }

        return $classConfiguration;
    }
}
