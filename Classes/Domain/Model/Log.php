<?php
namespace SPL\SplCleanupTools\Domain\Model;

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
 * Class Log
 *
 * @package SPL\SplCleanupTools\Domain\Model
 * @author Christian Reifenscheid
 */
class Log extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * backups
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SPL\SplCleanupTools\Domain\Model\Backup>
     * @cascade remove
     */
    protected $backups;
    
    /**
     * execution_context
     * 
     * @var integer
     */
    protected $executionContext = 0;
    
    /**
     * service
     * 
     * @var string
     */
    protected $service = '';
    
    /**
     * method
     * 
     * @var string
     */
    protected $method = '';
    
    /**
     * state
     * 
     * @var bool
     */
    protected $state = 1;
    
    /**
     * crdate
     * 
     * @var integer
     */
    protected $crdate = 0;
    
    /**
     * cruser_id
     * 
     * @var integer
     */
    protected $cruserId = 0;
    
    /**
     * cruser
     * 
     * @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser
     */
    protected $cruser;
    
    /**
    * __construct
    */
    public function __construct()
    {
        $this->initStorageObjects();
    }
    
    /**
     * @return void
     */
    protected function initStorageObjects() : void
    {
        $this->backups = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }
    
    /*
     * Add a backup
     *
     * @return void
     */
    public function addBackup(\SPL\SplCleanupTools\Domain\Model\Backup $backup) : void
    {
        $this->backups->attach($backup);
    }
    
    /*
     * Remove a backup
     *
     * @return void
     */
    public function removeBackup(\SPL\SplCleanupTools\Domain\Model\Backup $backupToRemove) : void
    {
        $this->backups->detach($backupToRemove);
    }
    
    /*
     * Returns the backups
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getBackups() : \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->backups;
    }
    
    /**
     * Sets the backups
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $backups
     * @return void
     */
    public function setBackups(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $backups) : void
    {
        $this->backups = $backups;
    }

    /**
     * Returns execution_context 
     * 
     * @return int
     */
    public function getExecutionContext() : int
    {
        return $this->executionContext;
    }

    /**
     * Sets execution_context
     * 
     * @param int $executionContext
     * 
     * @return void
     */
    public function setExecutionContext(int $executionContext) : void
    {
        $this->executionContext = $executionContext;
    }

    /**
     * Returns the service
     * 
     * @return null|string
     */
    public function getService() : ?string
    {
        return $this->service;
    }

    /**
     * Sets the service
     * 
     * @param string $service
     * 
     * @return void
     */
    public function setService(string $service) : void
    {
        $this->service = $service;
    }

    /**
     * Returns the method
     * 
     * @return null|string
     */
    public function getMethod() : ?string
    {
        return $this->method;
    }

    /**
     * Sets the method
     * 
     * @param string $method
     * 
     * @return void
     */
    public function setMethod($method) : void
    {
        $this->method = $method;
    }
    
    /**
     * Returns the state
     * 
     * @return boolean
     */
    public function getState() : bool
    {
        return $this->state;
    }

    /**
     * Sets the state
     * 
     * @param boolean $state
     * 
     * @return void
     */
    public function setState($state) : void
    {
        $this->state = $state;
    }

    /**
     * Returns crdate
     * 
     * @return number
     */
    public function getCrdate() : int
    {
        return $this->crdate;
    }
    
    /**
     * Sets crdate
     * 
     * @param number $crdate
     * 
     * @return void
     */
    public function setCrdate(int $crdate) : void
    {
        $this->crdate = $crdate;
    }
    
    /**
     * Returns cruser_id
     * 
     * @return number
     */
    public function getCruserId() : int
    {
        return $this->cruserId;
    }
    
    /**
     * Sets cruser_id
     * 
     * @param number $cruserId
     * 
     * @return void
     */
    public function setCruserId(int $cruserId) : void
    {
        $this->cruserId = $cruserId;
    }
    
    /**
     * Return BE user object of cruser
     *
     * @return \TYPO3\CMS\Beuser\Domain\Model\BackendUser
     */
    public function getCruser () : \TYPO3\CMS\Beuser\Domain\Model\BackendUser
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $beUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        /** @var \TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository $beUserRepository */
        $beUserRepository = $objectManager->get(\TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository::class);
        return $beUserRepository->findByUid($this->getCruserId());
    }
}
