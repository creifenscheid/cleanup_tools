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
 * Class Backup
 *
 * @package SPL\SplCleanupTools\Domain\Model
 * @author Christian Reifenscheid
 */
class Backup extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * originalUid
     * 
     * @var integer
     */
    protected $originalUid = 0;
    
    /**
     * table
     * 
     * @var string
     */
    protected $table = '';
    
    /**
     * data
     * 
     * @var string
     */
    protected $data = '';
    
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
     * Returns the orignal uid
     * 
     * @return null|integer
     */
    public function getOriginalUid() : ?int
    {
        return $this->originalUid;
    }

    /**
     * Sets the original uid
     * 
     * @param integer $originalUid
     * 
     * @return void
     */
    public function setOriginalUid(int $originalUid) : void
    {
        $this->originalUid = $originalUid;
    }

    /**
     * Returns the table
     * 
     * @return null|string
     */
    public function getTable() : ?string
    {
        return $this->table;
    }

    /**
     * Sets the table
     * 
     * @param string $table
     * 
     * @return void
     */
    public function setTable(string $table) : void
    {
        $this->table = $table;
    }

    /**
     * Returns the data
     * 
     * @return null|string
     */
    public function getData() : ?string
    {
        return $this->data;
    }

    /**
     * Sets the data
     * 
     * @param string $data
     * 
     * @return void
     */
    public function setData($data) : void
    {
        $this->data = $data;
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
