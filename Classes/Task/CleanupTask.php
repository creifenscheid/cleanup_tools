<?php

namespace SPL\SplCleanupTools\Task;

use SPL\SplCleanupTools\Service\CleanupService;
use SPL\SplCleanupTools\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

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
 * @package SPL\SplCleanupTools\Task
 * @author  Christian Reifenscheid
 */
class CleanupTask extends AbstractTask
{
    /**
     * @var string
     */
    protected $cleanupMethod = '';

    /**
     * Execute function
     *
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function execute() : bool
    {
        /** @var \SPL\SplCleanupTools\Service\CleanupService $cleanupService */
        $cleanupService = GeneralUtility::makeInstance(CleanupService::class);
        $cleanupService->setExecutionContext(CleanupService::EXECUTION_CONTEXT_SCHEDULER);

        /** @var \SPL\SplCleanupTools\Service\ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        // get method configuration
        $methodConfiguration = $configurationService->getMethodConfiguration($this->cleanupMethod);

        // check if parameters are configured
        if ($methodConfiguration['parameterConfiguration']) {
            $parameters = $this->convertParameters($methodConfiguration['parameterConfiguration']);

            // process method through cleanup utility with parameters
            return $cleanupService->processMethod($this->cleanupMethod, $parameters);
        }

        // process method through cleanup utility
        return $cleanupService->processMethod($this->cleanupMethod);
    }

    /**
     * Returns the cleanup method
     *
     * @return string
     */
    public function getCleanupMethod() : string
    {
        return $this->cleanupMethod;
    }

    /**
     * Sets the cleanup method
     *
     * @param string $cleanupMethod
     *
     * @return void
     */
    public function setCleanupMethod(string $cleanupMethod) : void
    {
        $this->cleanupMethod = $cleanupMethod;
    }

    /**
     * This method returns the selected table as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation() : string
    {
        /** @var \SPL\SplCleanupTools\Service\ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        return LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.information') . ' ' . $this->cleanupMethod;
    }

    /**
     * Convert configured parameters into array with keys and correct casted values
     *
     * @param array $parametersArray
     *
     * @return array
     */
    private function convertParameters(array $parametersArray) : array
    {
        $preparedParameters = [];

        foreach ($parametersArray as $parameter => $parameterConfiguration) {
            // cast given value
            $castedValue = $this->castValue($parameterConfiguration['value'], $parameterConfiguration['type']);

            // if value is given
            if ($castedValue) {
                // push parameter into result array
                $preparedParameters[$parameter] = $castedValue;
            }
        }

        return $preparedParameters;
    }

    /**
     * Function to convert a given value into the given data type
     *
     * @param        $value
     * @param string $type
     *
     * @return bool|int|string|null
     */
    private function castValue($value, string $type)
    {
        $result = null;

        switch ($type) {

            default:
                $result = $value ? : null;
                break;

            case 'string':
                $result = $value ? trim((string)$value) : null;
                break;

            case 'int':
            case 'integer':
                $result = $value ? (int)trim((string)$value) : null;
                break;

            case 'bool':
            case 'boolean':
                if ((string)$value === '1' || strtolower($value) === 'true') {
                    $result = true;
                } elseif ((string)$value === '0' || strtolower($value) === 'false') {
                    $result = false;
                }
                break;
        }

        return $result;
    }
}
