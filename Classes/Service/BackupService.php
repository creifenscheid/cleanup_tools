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
 * Class BackupService
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class BackupService
{
    /**
     * extension configuration
     *
     * @var array
     */
    protected $extensionConfiguration;
    
    /**
     * backup repository
     *
     * @var \SPL\SplCleanupTools\Domain\Repository\BackupRepository
     */
    protected $backupRepository;
    
    /**
     * Inject backup repository
     *
     * @var \SPL\SplCleanupTools\Domain\Repository\BackupRepository $backupRepository
     *
     * @return void
     */
    public function injectBackupRepository(\SPL\SplCleanupTools\Domain\Repository\BackupRepository $backupRepository) : void 
    {
        $this->backupRepository = $backupRepository;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // get extension configuration
        $this->extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('spl_cleanup_tools');
    }
    
    /**
     * Create backup of given element
     *
     * @param array  $element
     * @param string $table
     *
     * @return void
     */
    public function backup(array $element, string $table) : void
    {
        // if auto backup is enabled
        if ($this->extensionConfiguration['enableAutoBackup']) {
            // init backup element
            /** @var \SPL\SplCleanupTools\Domain\Model\Backup $backup */
            $backup = new \SPL\SplCleanupTools\Domain\Model\Backup();

            // set backup information
            $backup->setLog($this->log);
            $backup->setOriginalUid($element['uid']);
            $backup->setTable($table);
            $backup->setData(serialize($element));

            // add backup to log
            $this->log->addBackup($backup);
        }
    }
    
    /**
     * Restore element from given backup
     *
     * @param \SPL\SplCleanupTools\Domain\Model\Backup $backup
     * @return void
     */
    public function restore(\SPL\SplCleanupTools\Domain\Model\Backup $backup) : void
    {
        // initialze data handler
        $dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        
        // get data from backup
        $data = unserialize($backup->getData());
        #unset('uid', $data);
        
        // todo
        $dataHandler->start($data, []);
        
        // process dataHandler
        $dataHandler->process_datamap();

        // set restored flag
        $backup->setRestored(true);
        $this->backupRepository->update($backup);
    }
}