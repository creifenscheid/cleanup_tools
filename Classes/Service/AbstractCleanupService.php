<?php
namespace ChristianReifenscheid\CleanupTools\Service;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
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
 * @packagee ChristianReifenscheid\CleanupTools\Service
 * @author Christian Reifenscheid
 */
abstract class AbstractCleanupService
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * Execute cleanup process
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    abstract public function execute(): \TYPO3\CMS\Core\Messaging\FlashMessage;

    /*
     * dry run
     *
     * @var boolean
     */
    protected $dryRun = true;

    /**
     * log
     *
     * @var \ChristianReifenscheid\CleanupTools\Domain\Model\Log
     */
    protected $log;

    /**
     * Sets dry run
     *
     * @param bool $dryRun
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Returns log
     *
     * @return \ChristianReifenscheid\CleanupTools\Domain\Model\Log
     */
    public function getLog(): \ChristianReifenscheid\CleanupTools\Domain\Model\Log
    {
        return $this->log;
    }

    /**
     * Sets log
     *
     * @param \ChristianReifenscheid\CleanupTools\Domain\Model\Log $log
     * @return void
     */
    public function setLog(\ChristianReifenscheid\CleanupTools\Domain\Model\Log $log): void
    {
        $this->log = $log;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // init object manager
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    }

    /**
     * Create and add logMessage object
     *
     * @param string $message
     */
    protected function addMessage(string $message): void
    {
        // create new message
        // todo: del if objectmngr wrks
        //$newLogMessage = new LogMessage();
        $newLogMessage = $this->objectManager->get(\ChristianReifenscheid\CleanupTools\Domain\Model\LogMessage::class);
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
    protected function addLLLMessage(string $key, array $arguments = null): void
    {
        // create new message
        // todo: del if objectmngr wrks
        //$newLogMessage = new LogMessage();
        $newLogMessage = $this->objectManager->get(\ChristianReifenscheid\CleanupTools\Domain\Model\LogMessage::class);
        
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
     * @param int $severity
     * @param null|string $message
     * @param null|string $headline
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    protected function createFlashMessage(int $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::OK, string $message = null, $headline = null): \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        // define headline
        $headline = $headline ?: \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.fallback.headline', 'CleanupTools');

        // define message
        $message = $message ?: \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.message', 'CleanupTools');

        // initialize and return flash message object
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class, $message, $headline, $severity);
    }
}
