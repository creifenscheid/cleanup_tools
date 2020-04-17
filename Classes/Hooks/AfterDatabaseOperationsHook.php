<?php
namespace SPL\SplCleanupTools\Hooks;

use SPL\SplCleanupTools\Service\CleanupService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use SPL\SplCleanupTools\Service\CleanFlexFormsService;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
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
 * Class AfterDatabaseOperationsHook
 *
 * @package SPL\SplCleanupTools\Hooks
 * @author Christian Reifenscheid
 */
class AfterDatabaseOperationsHook
{

    /**
     * processDatamap_afterDatabaseOperations
     *
     * @param string $status
     * @param string $table
     * @param integer $recordUid
     * @param array $fields
     *
     * @return bool|FlashMessage
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $recordUid, $fields)
    {
        // define field to check on
        $fieldName = 'pi_flexform';

        // if an content element is update in field pi_flexform
        if ($status === 'update' && $table === 'tt_content' && array_key_exists($fieldName, $fields)) {

            /** @var \SPL\SplCleanupTools\Service\CleanupService $cleanupService */
            $cleanupService = GeneralUtility::makeInstance(CleanupService::class);
            $cleanupService->setExecutionContext(CleanupService::EXECUTION_CONTEXT_DBHOOK);

            // set execution mode
            $cleanupService->setExecutionMode(CleanupService::USE_METHOD_PROPERTIES);

            // process method through cleanup utility
            // disable dry run
            $cleanupService->setDryRun(false);

            // process
            return $cleanupService->process(CleanFlexFormsService::class, 'executeByUid', [
                'recordUid' => (int) $recordUid
            ]);
        }

        return false;
    }
}