<?php

namespace SPL\SplCleanupTools\Service;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class BaseService
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class BaseService
{
    /**
     * $log
     *
     * @var \SPL\SplCleanupTools\Domain\Model\Log
     */
    protected $log;

    /**
     * @var \SPL\SplCleanupTools\Service\BackupService
     */
    protected $backupService;

    /**
     * Constructor
     */
    public function __construct()
    {
        // initialize backup service
        $this->backupService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Service\BackupService::class);
    }

    /**
     * Returns the log
     *
     * @return \SPL\SplCleanupTools\Domain\Model\Log
     */
    public function getLog() : \SPL\SplCleanupTools\Domain\Model\Log
    {
        return $this->log;
    }

    /**
     * Sets the log
     *
     * @param \SPL\SplCleanupTools\Domain\Model\Log $log
     *
     * @return void
     */
    public function setLog(\SPL\SplCleanupTools\Domain\Model\Log $log) : void
    {
        $this->log = $log;
    }
}
