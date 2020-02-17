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
     * @var \SPL\SplCleanupTools\Domain\Model\Backup
     */
    protected $backups;
    
    /**
     * processing_context
     * 
     * @var integer
     */
    protected $processingContext = 0;
    
    /**
     * utility
     * 
     * @var string
     */
    protected $utility = '';
    
    /**
     * action
     * 
     * @var string
     */
    protected $action = '';
    
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
    
    /*
     * Returns the backups
     *
     * @return \SPL\SplCleanupTools\Domain\Model\Backup
     */
    public function getBackups() : \SPL\SplCleanupTools\Domain\Model\Backup
    {
        return $this->backups;
    }
    
    /**
     * Sets the backups
     *
     * @param \SPL\SplCleanupTools\Domain\Model\Backup $backups
     * @return void
     */
    public function setBackups(\SPL\SplCleanupTools\Domain\Model\Backup $backups) : void
    {
        $this->backups = $backups;
    }

    /**
     * Returns $processingContext 
     * 
     * @return int
     */
    public function getProcessingContext() : int
    {
        return $this->processingContext;
    }

    /**
     * Sets processingContext
     * 
     * @param int $processingContext
     * 
     * @return void
     */
    public function setProcessingContext(int $processingContext) : void
    {
        $this->processingContext = $processingContext;
    }

    /**
     * Returns the utility
     * 
     * @return null|string
     */
    public function getUtility() : ?string
    {
        return $this->utility;
    }

    /**
     * Sets the utility
     * 
     * @param string $utility
     * 
     * @return void
     */
    public function setUtility(string $utility) : void
    {
        $this->utility = $utility;
    }

    /**
     * Returns the action
     * 
     * @return null|string
     */
    public function getAction() : ?string
    {
        return $this->action;
    }

    /**
     * Sets the action
     * 
     * @param string $data
     * 
     * @return void
     */
    public function setAction($action) : void
    {
        $this->action = $action;
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
}
