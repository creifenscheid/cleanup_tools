<?php
declare(strict_types = 1);
namespace SPL\SplCleanupTools\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

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
 * Class DeletedRecordsService
 * Force-deletes all records in the database which have a deleted=1 flag
 * Originally taken from: \TYPO3\CMS\Lowlevel\Command\DeletedRecordsCommand::class
 *
 * @package SPL\SplCleanupTools\Service
 * @author Christian Reifenscheid
 */
class DeletedRecordsService
{
    /**
     * Find and permanently delete records which are marked as deleted
     *
     * @param int $pid
     * @param int $depth
     * @param bool $dryRun
     */
    public function execute(int $pid = 0, int $depth = 1000, bool $dryRun = true)
    {
        $startingPoint = MathUtility::forceIntegerInRange($pid, 0);
        $depth = MathUtility::forceIntegerInRange($depth, 0);

        // find all records that should be deleted
        $deletedRecords = $this->findAllFlaggedRecordsInPage($startingPoint, $depth);
        
        $totalAmountOfTables = count($deletedRecords);
        $totalAmountOfRecords = 0;
        foreach ($deletedRecords as $tableName => $itemsInTable) {
            $totalAmountOfRecords += count($itemsInTable);
        }
        
        if ($dryRun) {
            return count($totalAmountOfRecords);
        }

        // actually permanently delete them
        $this->deleteRecords($deletedRecords);

        // ToDo: 'All done!
    }

    /**
     * Recursive traversal of page tree to fetch all records marekd as "deleted",
     * via option $GLOBALS[TCA][$tableName][ctrl][delete]
     * This also takes deleted versioned records into account.
     *
     * @param int $pageId the uid of the pages record (can also be 0)
     * @param int $depth The current depth of levels to go down
     * @param array $deletedRecords the records that are already marked as deleted (used when going recursive)
     *
     * @return array the modified $deletedRecords array
     */
    protected function findAllFlaggedRecordsInPage(int $pageId, int $depth, array $deletedRecords = []): array
    {
        /** @var QueryBuilder $queryBuilderForPages */
        $queryBuilderForPages = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilderForPages->getRestrictions()->removeAll();

        $pageId = (int)$pageId;
        if ($pageId > 0) {
            $queryBuilderForPages
                ->select('uid', 'deleted')
                ->from('pages')
                ->where(
                    $queryBuilderForPages->expr()->andX(
                        $queryBuilderForPages->expr()->eq(
                            'uid',
                            $queryBuilderForPages->createNamedParameter($pageId, \PDO::PARAM_INT)
                        ),
                        $queryBuilderForPages->expr()->neq('deleted', 0)
                    )
                )
                ->execute();
            $rowCount = $queryBuilderForPages
                ->count('uid')
                ->execute()
                ->fetchColumn(0);
            // Register if page itself is deleted
            if ($rowCount > 0) {
                $deletedRecords['pages'][$pageId] = $pageId;
            }
        }

        $databaseTables = $this->getTablesWithDeletedFlags();
        // Traverse tables of records that belongs to page
        foreach ($databaseTables as $tableName => $deletedField) {
            // Select all records belonging to page
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($tableName);

            $queryBuilder->getRestrictions()->removeAll();

            $result = $queryBuilder
                ->select('uid', $deletedField)
                ->from($tableName)
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)
                )
                )
                ->execute();

            while ($recordOnPage = $result->fetch()) {
                // Register record as deleted
                if ($recordOnPage[$deletedField]) {
                    $deletedRecords[$tableName][$recordOnPage['uid']] = $recordOnPage['uid'];
                }
                // Add any versions of those records
                $versions = BackendUtility::selectVersionsOfRecord(
                    $tableName,
                    $recordOnPage['uid'],
                    'uid,t3ver_wsid,t3ver_count,' . $deletedField,
                    null,
                    true
                ) ?: [];
                if (is_array($versions)) {
                    foreach ($versions as $verRec) {
                        // Mark as deleted
                        if (!$verRec['_CURRENT_VERSION'] && $verRec[$deletedField]) {
                            $deletedRecords[$tableName][$verRec['uid']] = $verRec['uid'];
                        }
                    }
                }
            }
        }

        // Find subpages to root ID and go recursive
        if ($depth > 0) {
            $depth--;
            $result = $queryBuilderForPages
                ->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilderForPages->expr()->eq('pid', $pageId)
                )
                ->orderBy('sorting')
                ->execute();

            while ($subPage = $result->fetch()) {
                $deletedRecords = $this->findAllFlaggedRecordsInPage($subPage['uid'], $depth, $deletedRecords);
            }
        }

        // Add any versions of the page
        if ($pageId > 0) {
            $versions = BackendUtility::selectVersionsOfRecord(
                'pages',
                $pageId,
                'uid,t3ver_oid,t3ver_wsid,t3ver_count',
                null,
                true
            ) ?: [];
            if (is_array($versions)) {
                foreach ($versions as $verRec) {
                    if (!$verRec['_CURRENT_VERSION']) {
                        $deletedRecords = $this->findAllFlaggedRecordsInPage($verRec['uid'], $depth, $deletedRecords);
                    }
                }
            }
        }

        return $deletedRecords;
    }

    /**
     * Fetches all tables registered in the TCA with a deleted
     * and that are not pages (which are handled separately)
     *
     * @return array an associative array with the table as key and the
     */
    protected function getTablesWithDeletedFlags(): array
    {
        $tables = [];
        foreach ($GLOBALS['TCA'] as $tableName => $configuration) {
            if ($tableName !== 'pages' && isset($GLOBALS['TCA'][$tableName]['ctrl']['delete'])) {
                $tables[$tableName] = $GLOBALS['TCA'][$tableName]['ctrl']['delete'];
            }
        }
        ksort($tables);
        return $tables;
    }

    /**
     * Deletes records via DataHandler
     *
     * @param array $deletedRecords two level array with tables and uids
     */
    protected function deleteRecords(array $deletedRecords)
    {
        // Putting "pages" table in the bottom
        if (isset($deletedRecords['pages'])) {
            $_pages = $deletedRecords['pages'];
            unset($deletedRecords['pages']);
            // To delete sub pages first assuming they are accumulated from top of page tree.
            $deletedRecords['pages'] = array_reverse($_pages);
        }

        // set up the data handler instance
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], []);

        // Loop through all tables and their records
        foreach ($deletedRecords as $table => $list) {
            // ToDo:Ausgabe 'Flushing ' . count($list) . ' deleted records from table "' . $table . '"');
            
            foreach ($list as $uid) {
                // Notice, we are deleting pages with no regard to subpages/subrecords - we do this since they
                // should also be included in the set of deleted pages of course (no un-deleted record can exist
                // under a deleted page...)
                $dataHandler->deleteRecord($table, $uid, true, true);
                // Return errors if any:
                if (!empty($dataHandler->errorLog)) {
                    $errorMessage = array_merge(['DataHandler reported an error'], $dataHandler->errorLog);
                    // ToDo:Ausgabe $errorMessage
                } elseif (!$io->isQuiet()) {
                    // ToDo:Ausgabe 'Permanently deleted record "' . $table . ':' . $uid . '".'
                }
            }
        }
    }
}
