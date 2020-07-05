<?php
declare(strict_types = 1);
namespace ChristianReifenscheid\CleanupTools\Service;

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
 * Class OrphanRecordsService
 * Finds (and fixes) all records that have an invalid / deleted page ID
 *
 * @see \TYPO3\CMS\Lowlevel\Command\OrphanRecordsCommand::class
 *
 * @package ChristianReifenscheid\CleanupTools\Service
 * @author Christian Reifenscheid
 */
class OrphanRecordsService extends AbstractCleanupService
{
    /**
     * Executes the command to find records not attached to the pagetree
     * and permanently delete these records
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function execute(): \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        // find all records that should be deleted
        $allRecords = $this->findAllConnectedRecordsInPage(0, 10000);

        // Find orphans
        $orphans = [];
        foreach (array_keys($GLOBALS['TCA']) as $tableName) {
            $idList = [
                0
            ];
            if (is_array($allRecords[$tableName]) && ! empty($allRecords[$tableName])) {
                $idList = $allRecords[$tableName];
            }
            // Select all records that are NOT connected
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable($tableName);

            $result = $queryBuilder->select('uid')
                ->from($tableName)
                ->where($queryBuilder->expr()
                ->notIn('uid', 
                // do not use named parameter here as the list can get too long
                array_map('intval', $idList)))
                ->orderBy('uid')
                ->execute();

            $rowCount = $queryBuilder->count('uid')
                ->execute()
                ->fetchColumn(0);
            if ($rowCount) {
                $orphans[$tableName] = [];
                while ($orphanRecord = $result->fetch()) {
                    $orphans[$tableName][$orphanRecord['uid']] = $orphanRecord['uid'];
                }
            }
        }

        if (count($orphans)) {
            if ($this->dryRun) {
                $message = count($orphans) . ' orphan records found.';
                $this->addMessage($message);
                return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::INFO, $message);
            } else {
                // Actually permanently delete them
                return $this->deleteRecords($orphans);
            }
        } else {
            $message = 'No orphan records found.';
            $this->addMessage($message);
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::OK, $message);
        }
    }

    /**
     * Recursive traversal of page tree to fetch all records marekd as "deleted",
     * via option $GLOBALS[TCA][$tableName][ctrl][delete]
     * This also takes deleted versioned records into account.
     *
     * @param int $pageId
     *            the uid of the pages record (can also be 0)
     * @param int $depth
     *            The current depth of levels to go down
     * @param array $allRecords
     *            the records that are already marked as deleted (used when going recursive)
     *            
     * @return array the modified $deletedRecords array
     */
    protected function findAllConnectedRecordsInPage(int $pageId, int $depth, array $allRecords = []): array
    {
        // Register page
        if ($pageId > 0) {
            $allRecords['pages'][$pageId] = $pageId;
        }
        // Traverse tables of records that belongs to page
        foreach (array_keys($GLOBALS['TCA']) as $tableName) {
            if ($tableName !== 'pages') {
                // Select all records belonging to page:
                $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable($tableName);

                $queryBuilder->getRestrictions()->removeAll();

                $result = $queryBuilder->select('uid')
                    ->from($tableName)
                    ->where($queryBuilder->expr()
                    ->eq('pid', $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)))
                    ->execute();

                while ($rowSub = $result->fetch()) {
                    $allRecords[$tableName][$rowSub['uid']] = $rowSub['uid'];
                    // Add any versions of those records:
                    $versions = \TYPO3\CMS\Backend\Utility\BackendUtility::selectVersionsOfRecord($tableName, $rowSub['uid'], 'uid,t3ver_wsid,t3ver_count', null, true);
                    if (is_array($versions)) {
                        foreach ($versions as $verRec) {
                            if (! $verRec['_CURRENT_VERSION']) {
                                $allRecords[$tableName][$verRec['uid']] = $verRec['uid'];
                            }
                        }
                    }
                }
            }
        }
        // Find subpages to root ID and traverse (only when rootID is not a version or is a branch-version):
        if ($depth > 0) {
            $depth --;
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('pages');

            $queryBuilder->getRestrictions()->removeAll();

            $result = $queryBuilder->select('uid')
                ->from('pages')
                ->where($queryBuilder->expr()
                ->eq('pid', $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)))
                ->orderBy('sorting')
                ->execute();

            while ($row = $result->fetch()) {
                $allRecords = $this->findAllConnectedRecordsInPage((int) $row['uid'], $depth, $allRecords);
            }
        }

        // Add any versions of pages
        if ($pageId > 0) {
            $versions = \TYPO3\CMS\Backend\Utility\BackendUtility::selectVersionsOfRecord('pages', $pageId, 'uid,t3ver_oid,t3ver_wsid,t3ver_count', null, true);
            if (is_array($versions)) {
                foreach ($versions as $verRec) {
                    if (! $verRec['_CURRENT_VERSION']) {
                        $allRecords = $this->findAllConnectedRecordsInPage((int) $verRec['uid'], $depth, $allRecords);
                    }
                }
            }
        }
        return $allRecords;
    }

    /**
     * Deletes records via DataHandler
     *
     * @param array $orphanedRecords
     *            two level array with tables and uids
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    protected function deleteRecords(array $orphanedRecords) : \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        // Putting "pages" table in the bottom
        if (isset($orphanedRecords['pages'])) {
            $_pages = $orphanedRecords['pages'];
            unset($orphanedRecords['pages']);
            // To delete sub pages first assuming they are accumulated from top of page tree.
            $orphanedRecords['pages'] = array_reverse($_pages);
        }

        // set up the data handler instance
        $dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        // error counter
        $errors = 0;

        $dataHandler->start([], []);

        // Loop through all tables and their records
        foreach ($orphanedRecords as $table => $list) {
            $this->addMessage('Flushing ' . count($list) . ' orphaned records from table "' . $table . '"');
            foreach ($list as $uid) {
                // Notice, we are deleting pages with no regard to subpages/subrecords - we do this since they
                // should also be included in the set of deleted pages of course (no un-deleted record can exist
                // under a deleted page...)
                $dataHandler->deleteRecord($table, $uid, true, true);
                // Return errors if any:
                if (! empty($dataHandler->errorLog)) {
                    $errorMessage = array_merge([
                        'DataHandler reported an error'
                    ], $dataHandler->errorLog);
                    $this->addMessage($errorMessage);
                    $errors ++;
                } else {
                    $this->addMessage('Permanently deleted orphaned record "' . $table . ':' . $uid . '".');
                }
            }
        }

        if ($errors > 0) {
            $message = 'While executing ' . __CLASS__ . ' ' . $errors . ' occured.';
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::WARNING, $message);
        }

        return $this->createFlashMessage();
    }
}