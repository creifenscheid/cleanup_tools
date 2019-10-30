<?php

namespace SPL\SplCleanupTools\Task;

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
class CleanupTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    /**
     * @var string
     */
    protected $cleanupAction;

    /**
     * @return bool
     */
    public function execute() : bool
    {
        $cleanupUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\CleanupUtility::class);
    
        // process action through cleanup utility
        return $cleanupUtility->processAction($this->cleanupAction);
    }

    /**
     * Returns the cleanup action
     *
     * @return string
     */
    public function getCleanupAction() : string
    {
        return $this->cleanupAction;
    }

    /**
     * Sets the cleanup action
     *
     * @param string $cleanupAction
     *
     * @return void
     */
    public function setCleanupAction(string $cleanupAction) : void
    {
        $this->cleanupAction = $cleanupAction;
    }

    /**
     * This method returns the selected table as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation() : string
    {
        return 'Cleanup Action: ' . $this->cleanupAction;
    }
}
