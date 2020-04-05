<?php

namespace SPL\SplCleanupTools\Service;

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
 * Class AbstractCleanupService
 *
 * @packagee SPL\SplCleanupTools\Service
 * @author   Christian Reifenscheid
 */
abstract class AbstractCleanupService
{
    /**
     * Execute cleanup process
     */
    abstract public function execute();

    /*
     * dry run
     *
     * @var boolean
     */
    protected $dryRun = true;
    
    /**
     * log
     *
     * @var \SPL\SplCleanupTools\Domain\Model\Log
     */
    protected $log;

    /**
     * Returns dry run
     *
     * @return bool
     */
    public function getDryRun() : bool
    {
        return $this->dryRun;
    }

    /**
     * Sets dry run
     *
     * @param bool $dryRun
     */
    public function setDryRun(bool $dryRun) : void
    {
        $this->dryRun = $dryRun;
    }
    
    /**
     * Returns log
     *
     * @return \SPL\SplCleanupTools\Domain\Model\Log
     */
    public function getLog() : \SPL\SplCleanupTools\Domain\Model\Log
    {
        return $this->log;
    }
    
    /**
     * Sets log
     *
     * @param \SPL\SplCleanupTools\Domain\Model\Log $log
     * @return void
     */
    public function setLog(\SPL\SplCleanupTools\Domain\Model\Log $log) : void
    {
        $this->log = $log;
    }
    
    /**
     * Create and add logMessage object
     * 
     * @param string $message
     */
    protected function addMessage(string $message) : void
    {
        // create new message
        $newLogMessage = new \SPL\SplCleanupTools\Domain\Model\LogMessage();
        $newLogMessage->setLog($this->log);
        $newLogMessage->setMessage($message);
        
        // add message to log
        $this->log->addMessage($newLogMessage);
    }
    
    /**
     * Create and add logMessage object with localization key
     * 
     * @param string $key
     * @param null|array $arguments
     */
    protected function addMessage(string $key, array $arguments = null) : void
    {
        // create new message
        $newLogMessage = new \SPL\SplCleanupTools\Domain\Model\LogMessage();
        $newLogMessage->setLog($this->log);
        $newLogMessage->setLocalLangKey($key);
        
        if ($arguments) {
            $newLogMessage->setLocalLangArguments($arguments);
        }
        
        // add message to log
        $this->log->addMessage($newLogMessage);
    }
}
