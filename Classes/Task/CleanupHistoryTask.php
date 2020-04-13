<?php

namespace SPL\SplCleanupTools\Task;

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
 * Class CleanupHistoryTask
 *
 * @package SPL\SplCleanupTools\Task
 * @author  Christian Reifenscheid
 */
class CleanupHistoryTask extends AbstractTask
{
    /**
     * If set, entries marked as "deleted" are dropped from the table
     *
     * @var bool
     */
    protected $dropAlreadyDeleted = true;
    
    /**
     * Lifetime of log entries
     *
     * @var string
     */
    protected $logLifetime = '1 year';
    
    /**
     * Execute function
     *
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function execute() : bool
    {
        return true;
    }
    
    /**
     * Returns if marked entries shall be deleted
     *
     * @return bool
     */
    public function getDropAlreadyDeleted() : string
    {
        return $this->dropAlreadyDeleted;
    }
    
    /**
     * Sets if marked entries shall be deleted
     *
     * @param bool $dropAlreadyDeleted
     *
     * @return void
     */
    public function setDropAlreadyDeleted(bool $dropAlreadyDeleted) : void
    {
        $this->dropAlreadyDeleted = $dropAlreadyDeleted;
    }
    
    /**
     * Return log lifetime
     *
     * @return string
     */
    public function getLogLifetime () : string
    {
        return $this->logLifetime;
    }
    
    
    /**
     * Set log lifetime
     *
     * @param string $logLifetime
     * @return void
     */
    public function setLogLifetime (string $logLifetime) : void
    {
        $this->logLifetime = $logLifetime;
    }
}
