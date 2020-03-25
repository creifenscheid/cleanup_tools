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
    protected $serviceToProcess = '';

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

        // process method through cleanup service
        return $cleanupService->process($this->serviceToProcess, $ConfigurationService::FUNCTION_MAIN);
    }

    /**
     * Returns the service to process
     *
     * @return string
     */
    public function getServiceToProcess() : string
    {
        return $this->serviceToProcess;
    }

    /**
     * Sets the service to process
     *
     * @param string $serviceToProcess
     *
     * @return void
     */
    public function setServiceToProcess(string $serviceToProcess) : void
    {
        $this->serviceToProcess = $serviceToProcess;
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

        return LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.information') . ' ' . $this->serviceToProcess;
    }
}
