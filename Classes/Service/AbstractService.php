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
 * Class AbstractUtility
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
abstract class AbstractService
{
    /**
     * $log
     *
     * @var \SPL\SplCleanupTools\Domain\Model\Log
     */
    protected $log;

    /**
     * @var
     */
    protected $extensionConfiguration;

    public function __construct()
    {
        // get extension configuration
        $this->extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('spl_cleanup_tools');
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

    /**
     * @param array  $element
     * @param string $table
     */
    public function generateBackup($element, string $table) : void
    {
        // if auto backup is enabled
        if ($this->extensionConfiguration['enableAutoBackup']) {
            // init backup element
            /** @var \SPL\SplCleanupTools\Domain\Model\Backup $backup */
            $backup = new \SPL\SplCleanuptools\Domain\Model\Backup();

            // set backup information
            $backup->setLog($this->log);
            $backup->setOriginalUid($element['uid']);
            $backup->setTable($table);
            $backup->setData(serialize($element));

            // add backup to log
            $this->log->addBackup($backup);
        }
    }
}
