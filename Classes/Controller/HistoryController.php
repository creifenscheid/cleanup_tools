<?php
namespace creifenscheid\CleanupTools\Controller;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 C. Reifenscheid
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
 * @package creifenscheid\CleanupTools\Controller
 * @author C. Reifenscheid
 */
class HistoryController extends BaseController
{

    /**
     * Log repository
     *
     * @var \creifenscheid\CleanupTools\Domain\Repository\LogRepository
     */
    protected $logRepository;
    
    /**
     * Constructor
     *
     * @param \creifenscheid\CleanupTools\Domain\Repository\LogRepository $logRepository
     */
    public function __construct(\creifenscheid\CleanupTools\Domain\Repository\LogRepository $logRepository)
    {
        parent::__construct();
        $this->logRepository = $logRepository;
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
        $querySettings->setRespectStoragePage(false);
        // set query settings
        $this->logRepository->setDefaultQuerySettings($querySettings);

        // assign to the view
        $this->view->assignMultiple([
            'localizationFile' => $this->localizationFile,
            'logs' => $this->logRepository->findAll(),
            'logLifetimeOptions' => $this->configurationService->getLogLifetimeOptions()
        ]);
    }

    /**
     * Action to mark logs and messages as deleted based on given time
     *
     * @param string $logLifetime
     *            - Mark all entries with a >crdate as deleted
     *
     * @return void
     */
    public function cleanupAction(string $logLifetime): void
    {
        $cleanupService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\creifenscheid\CleanupTools\Service\CleanupService::class);
        
        $cleanupService->processHistoryCleanup($logLifetime);

        $this->redirect('index', 'History', 'CleanupTools');
    }
}
