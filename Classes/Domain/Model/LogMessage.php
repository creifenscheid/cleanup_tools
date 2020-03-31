<?php

namespace SPL\SplCleanupTools\Domain\Model;

use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

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
 * Class LogMessage
 *
 * @package SPL\SplCleanupTools\Domain\Model
 * @author  Christian Reifenscheid
 */
class LogMessage extends AbstractEntity
{
    /**
     * Log
     *
     * @var \SPL\SplCleanupTools\Domain\Model\Log
     */
    protected $log;
    
    /**
     * Message
     *
     * @var string
     */
    protected $message = '';
    
    /**
     * Returns log
     *
     * @return \SPL\SplCleanupTools\Domain\Model\Log
     */
    public function getLog () : \SPL\SplCleanupTools\Domain\Model\Log
    {
        return $this->log;
    }
    
    /**
     * Set log
     *
     * @var \SPL\SplCleanupTools\Domain\Model\Log $log
     * @return void
     */
    public function setLog (\SPL\SplCleanupTools\Domain\Model\Log $log) : void
    {
        $this->log = $log;
    }
    
    /**
     * Message
     *
     * @return string
     */
    public function getMessage () : string
    {
        return $this->message;
    }
    
    /**
     * Message
     *
     * @var string $message
     * @return void
     */
    public function setMessage (string $message) : void
    {
        $this->message = message;
    }
}