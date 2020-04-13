<?php

namespace SPL\SplCleanupTools\Controller;

use SPL\SplCleanupTools\Domain\Repository\LogRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

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
 * Class HistoryController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author  Christian Reifenscheid
 */
class HistoryController extends BaseController
{
    /**
     * Log repository
     *
     * @var \SPL\SplCleanupTools\Domain\Repository\LogRepository
     */
    protected $logRepository;

    /**
     * Inject log repository
     *
     * @param \SPL\SplCleanupTools\Domain\Repository\LogRepository $logRepository
     */
    public function injectLogRepository(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * action index
     *
     * @return void
     */
    public function indexAction() : void
    {
        // define query settings
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        // set query settings
        $this->logRepository->setDefaultQuerySettings($querySettings);

        // assign to the view
        $this->view->assignMultiple([
            'localizationFile' => $this->localizationFile,
            'logs' => $this->logRepository->findAll()
        ]);
    }
    
    /**
     * Action to mark logs and messages as deleted based on given time
     *
     * @param string $logLifetime - Mark all entries with a >crdate as deleted
     * @param bool $dropAlreadyDeleted - Deleted all entries marked as deleted
     */
    public function cleanupAction (string $logLifetime, bool $dropAlreadyDeleted) : void
    {
        
    }
}
