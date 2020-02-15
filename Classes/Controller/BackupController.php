<?php
namespace SPL\SplCleanupTools\Controller;

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
 * Class BackupController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author Christian Reifenscheid
 */
class BackupController extends \SPL\SplCleanupTools\Controller\BaseController
{
    /**
     * Backup repository
     * 
     * @var \SPL\SplCleanupTools\Domain\Repository\BackupRepository
     */
    protected $backupRepository;

    /**
     * Inject backup repository
     * 
     * @param \SPL\SplCleanupTools\Domain\Repository\BackupRepository $backupRepository
     */
    public function injectBackupRepository (\SPL\SplCleanupTools\Domain\Repository\BackupRepository $backupRepository) {
        $this->backupRepository = $backupRepository;
    }
    
    /**
     * action index
     *
     * @return void
     */
    public function indexAction(): void
    {
        // define query settings
        $querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(FALSE);
        // set query settings
        $this->backupRepository->setDefaultQuerySettings($querySettings);
        
        // assign to the view
        $this->view->assignMultiple([
            'localizationFile' => $this->configurationService->getLocalizationFile(),
            'backups' => $this->backupRepository->findAll()
        ]);
    }

    /**
     * Export db
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function exportDbAction(): void {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(__CLASS__ . ':' . __FUNCTION__ . '::' . __LINE__);

        $this->addFlashMessage(
            'Export',
            'Ok',
            \TYPO3\CMS\Core\Messaging\FlashMessage::OK
        );

        $this->forward('index');
    }

    /**
     * Import db
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function importDbAction(): void {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(__CLASS__ . ':' . __FUNCTION__ . '::' . __LINE__);

        $this->addFlashMessage(
            'Import',
            'Ok',
            \TYPO3\CMS\Core\Messaging\FlashMessage::OK
        );

        $this->forward('index');
    }
}
