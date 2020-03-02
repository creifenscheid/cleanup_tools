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
     * log repository
     *
     * @var \SPL\SplCleanupTools\Domain\Repository\LogRepository
     */
    protected $logRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        // get extension configuration
        $this->extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('spl_cleanup_tools');

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->backupRepository = $objectManager->get(\SPL\SplCleanupTools\Domain\Repository\BackupRepository::class);

        $this->logRepository = $objectManager->get(\SPL\SplCleanupTools\Domain\Repository\LogRepository::class);
    }

    /**
     * Create backup of given element
     *
     * @param array                                 $element
     * @param string                                $table
     * @param \SPL\SplCleanupTools\Domain\Model\Log $log
     *
     * @return void
     */
    public function backup(array $element, string $table, \SPL\SplCleanupTools\Domain\Model\Log $log) : void
    {
        // if auto backup is enabled
        if ($this->extensionConfiguration['enableAutoBackup']) {
            // init backup element
            /** @var \SPL\SplCleanupTools\Domain\Model\Backup $backup */
            $backup = new \SPL\SplCleanupTools\Domain\Model\Backup();

            // set backup information
            $backup->setLog($log);
            $backup->setOriginalUid($element['uid']);
            $backup->setTable($table);
            $backup->setData(serialize($element));

            // add backup to log
            $log->addBackup($backup);
        }
    }

    /**
     * Restore element from given backup
     *
     * @param \SPL\SplCleanupTools\Domain\Model\Backup $backup
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function restore(\SPL\SplCleanupTools\Domain\Model\Backup $backup) : void
    {
        // get data from backup
        $dataHandlerData[$backup->getTable()][$backup->getOriginalUid()] = unserialize($backup->getData(), [FALSE]);

        // init dataHandler
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        $dataHandler->start($dataHandlerData, []);
        $dataHandler->process_datamap();

        // set restored flag
        $backup->setRestored(true);
        $this->backupRepository->update($backup);

        // Log
        $restoreLog = new \SPL\SplCleanupTools\Domain\Model\Log();
        $restoreLog->setService(__CLASS__);
        $restoreLog->setAction(__FUNCTION__);
        $restoreLog->setCrdate(time());
        $restoreLog->setCruserId($GLOBALS['BE_USER']->user['uid']);
        $this->logRepository->add($restoreLog);
    }
}