<?php

namespace SPL\SplCleanupTools\Service;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    abstract public function execute() : \TYPO3\CMS\Core\Messaging\FlashMessage;

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
    protected function addLLLMessage(string $key, array $arguments = null) : void
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
    
    /**
     * Create flash messsage object 
     *
     * @param int $severityLevel
     * @param string $message
     * @param null|string $headline
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    protected function createFlashMessage (int $severityLevel, string $message, $headline = null) : \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        // define headline
        $headline = $headline ? : LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.fallback.headline', 'SplCleanupTools')
        
        // initialize and return flash message object
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
   $message,
   $headline,
   $severityLevel
);
    }
}
