<?php
namespace creifenscheid\CleanupTools\Task;

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
 * Class CleanupTask
 *
 * @package creifenscheid\CleanupTools\Task
 * @author C. Reifenscheid
 */
class HistoryTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    /**
     * @var string
     */
    protected $logLifetime = '';
    
    /**
     * Execute function
     *
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function execute(): bool
    {
        $cleanupService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\creifenscheid\CleanupTools\Service\CleanupService::class);
        
        return $cleanupService->processHistoryCleanup($this->logLifetime);
    }

    /**
     * Returns the log lifetime
     *
     * @return string
     */
    public function getLogLifetime(): string
    {
        return $this->logLifetime;
    }

    /**
     * Sets the log lifetime
     *
     * @param string $logLifetime
     *
     * @return void
     */
    public function setLogLifetime (string $logLifetime): void
    {
        $this->logLifetime = $logLifetime;
    }
}