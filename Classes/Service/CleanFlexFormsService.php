<?php
declare(strict_types=1);

namespace SPL\SplCleanupTools\Service;

use PDO;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
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
 * Class CleanFlexFormsService
 * Checks if TCA records with a FlexForm includes values that don't match the connected FlexForm value
 * Originally taken from: \TYPO3\CMS\Lowlevel\Command\CleanFlexFormsCommand::class
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class CleanFlexFormsService extends AbstractCleanupService
{
    /**
     * pid
     *
     * @var int $pid
     */
    protected $pid = 0;

    /**
     * depth
     *
     * @var int $depth
     */
    protected $depth = 1000;
    
    /**
     * recordUid
     * 
     * @var integer
     */
    protected $recordUid = 0;

    /**
     * Find and update records with FlexForms where the values do not match the datastructures
     *
     * @return int|bool
     */
    public function execute()
    {
        $startingPoint = MathUtility::forceIntegerInRange($this->pid, 0);
        $depth = MathUtility::forceIntegerInRange($this->depth, 0);

        // Find all records that should be updated
        $recordsToUpdate = $this->findAllDirtyFlexformsInPage($startingPoint, $depth);

        if ($this->dryRun) {
            return count($recordsToUpdate);
        } else {
            if (!empty($recordsToUpdate)) {
                // Clean up records
                return $this->cleanFlexFormRecords($recordsToUpdate);
            }
        }

        return true;
    }
    
    /**
     * Execute for defined element
     *
     * @param int $uid
     */
    public function executeByUid()
    {
        GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
    
        $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->isNotNull('pi_flexform')
            )->andWhere(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($recordUid, PDO::PARAM_INT))
            );

        $records = $queryBuilder->execute()->fetchAll();
        
        return $this->cleanFlexFormRecords($records);
    }
    
    /**
     * Validate given data
     *
     * @param array $data
     */
    public function isValid (array $data) : bool
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        
        $cleanFlexForm = $flexObj->cleanFlexFormXML('tt_content', 'pi_flexform', $data);
        
        return ($cleanFlexForm === $data['pi_flexform']);
    }

    /**
     * Recursive traversal of page tree
     *
     * @param int   $pageId              Page root id
     * @param int   $depth               Depth
     * @param array $dirtyFlexFormFields the list of all previously found flexform fields
     *
     * @return array
     */
    protected function findAllDirtyFlexformsInPage(int $pageId, int $depth, array $dirtyFlexFormFields = []) : array
    {
        if ($pageId > 0) {
            $dirtyFlexFormFields = $this->compareAllFlexFormsInRecord('pages', $pageId, $dirtyFlexFormFields);
        }

        // Traverse tables of records that belongs to this page
        foreach ($GLOBALS['TCA'] as $tableName => $tableConfiguration) {
            if ($tableName !== 'pages') {
                // Select all records belonging to page:
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);

                $queryBuilder->getRestrictions()->removeAll();

                $result = $queryBuilder->select('uid')
                    ->from($tableName)
                    ->where($queryBuilder->expr()
                        ->eq('pid', $queryBuilder->createNamedParameter($pageId, PDO::PARAM_INT)))
                    ->execute();

                while ($rowSub = $result->fetch()) {
                    // Traverse flexforms
                    $dirtyFlexFormFields = $this->compareAllFlexFormsInRecord($tableName, $rowSub['uid'], $dirtyFlexFormFields);
                    // Add any versions of those records
                    $versions = BackendUtility::selectVersionsOfRecord($tableName, $rowSub['uid'], 'uid,t3ver_wsid,t3ver_count', null, true);
                    if (is_array($versions)) {
                        foreach ($versions as $verRec) {
                            if (!$verRec['_CURRENT_VERSION']) {
                                // Traverse flexforms
                                $dirtyFlexFormFields = $this->compareAllFlexFormsInRecord($tableName, $verRec['uid'], $dirtyFlexFormFields);
                            }
                        }
                    }
                }
            }
        }

        // Find subpages
        if ($depth > 0) {
            $depth--;
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');

            $queryBuilder->getRestrictions()->removeAll();

            $result = $queryBuilder->select('uid')
                ->from('pages')
                ->where($queryBuilder->expr()
                    ->eq('pid', $queryBuilder->createNamedParameter($pageId, PDO::PARAM_INT)))
                ->orderBy('sorting')
                ->execute();

            while ($row = $result->fetch()) {
                $dirtyFlexFormFields = $this->findAllDirtyFlexformsInPage($row['uid'], $depth, $dirtyFlexFormFields);
            }
        }
        // Add any versions of pages
        if ($pageId > 0) {
            $versions = BackendUtility::selectVersionsOfRecord('pages', $pageId, 'uid,t3ver_oid,t3ver_wsid,t3ver_count', null, true);
            if (is_array($versions)) {
                foreach ($versions as $verRec) {
                    if (!$verRec['_CURRENT_VERSION']) {
                        $dirtyFlexFormFields = $this->findAllDirtyFlexformsInPage($verRec['uid'], $depth, $dirtyFlexFormFields);
                    }
                }
            }
        }

        return $dirtyFlexFormFields;
    }

    /**
     * Check a specific record on all TCA columns if they are FlexForms and if the FlexForm values
     * don't match to the newly defined ones.
     *
     * @param string $tableName           Table name
     * @param int    $uid                 UID of record in processing
     * @param array  $dirtyFlexFormFields the existing FlexForm fields
     *
     * @return array the updated list of dirty FlexForm fields
     */
    protected function compareAllFlexFormsInRecord(string $tableName, int $uid, array $dirtyFlexFormFields = []) : array
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        foreach ($GLOBALS['TCA'][$tableName]['columns'] as $columnName => $columnConfiguration) {
            if ($columnConfiguration['config']['type'] === 'flex') {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
                $queryBuilder->getRestrictions()->removeAll();

                $fullRecord = $queryBuilder->select('*')
                    ->from($tableName)
                    ->where($queryBuilder->expr()
                        ->eq('uid', $queryBuilder->createNamedParameter($uid, PDO::PARAM_INT)))
                    ->execute()
                    ->fetch();

                if ($fullRecord[$columnName]) {
                    // Clean XML and check against the record fetched from the database
                    $newXML = $flexObj->cleanFlexFormXML($tableName, $columnName, $fullRecord);
                    if (md5($fullRecord[$columnName]) !== md5($newXML)) {
                        $dirtyFlexFormFields[$tableName . ':' . $uid . ':' . $columnName] = $fullRecord;
                    }
                }
            }
        }

        return $dirtyFlexFormFields;
    }

    /**
     * Actually cleans the database record fields with a new FlexForm as chosen currently for this record
     *
     * @param array $records
     *
     * @return bool
     */
    protected function cleanFlexFormRecords(array $records) : bool
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);

        // Set up the data handler instance
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->dontProcessTransformations = true;
        $dataHandler->bypassWorkspaceRestrictions = true;
        $dataHandler->bypassFileHandling = true;
        // Setting this option allows to also update deleted records (or records on deleted pages) within DataHandler
        $dataHandler->bypassAccessCheckForRecords = true;

        // Loop through all tables and their records
        $errorOccurred = false;
        foreach ($records as $recordIdentifier => $fullRecord) {
            [$table, $uid, $field] = explode(':', $recordIdentifier);
            // Clean XML now
            $data = [];
            if ($fullRecord[$field]) {
                $data[$table][$uid][$field] = $flexObj->cleanFlexFormXML($table, $field, $fullRecord);
            } else {
                continue;
            }
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            // Return errors if any:
            if (!empty($dataHandler->errorLog)) {
                $errorMessage = array_merge([
                    'DataHandler reported an error'
                ], $dataHandler->errorLog);

                $this->addMessage($errorMessage);
                $errorOccurred = true;
            }
        }
        
        if ($errorOccurred) {
            return false;
        }
        
        return true;
    }

    /**
     * Returns pid
     *
     * @return int
     */
    public function getPid() : int
    {
        return $this->pid;
    }

    /**
     * Sets pid
     *
     * @param int $pid
     *
     * @return void
     */
    public function setPid(int $pid) : void
    {
        $this->pid = $pid;
    }

    /**
     * Returns depth
     *
     * @return int
     */
    public function getDepth() : int
    {
        return $this->depth;
    }

    /**
     * Sets depth
     *
     * @param int $depth
     *
     * @return void
     */
    public function setDepth(int $depth) : void
    {
        $this->depth = $depth;
    }
    
    /**
     * Returns recordUid
     *
     * @return int
     */
    public function getRecordUid() : int
    {
        return $this->recordUid;
    }
    
    /**
     * Sets recordUid
     *
     * @param int $recordUid
     *
     * @return void
     */
    public function setRecordUid(int $recordUid) : void
    {
        $this->recordUid = $recordUid;
    }
}
