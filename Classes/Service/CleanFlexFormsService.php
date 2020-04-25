<?php
declare(strict_types = 1);
namespace ChristianReifenscheid\CleanupTools\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use PDO;

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
 * Class CleanFlexFormsService
 * Checks if TCA records with a FlexForm includes values that don't match the connected FlexForm value
 *
 * @see \TYPO3\CMS\Lowlevel\Command\CleanFlexFormsCommand::class
 *
 * @package ChristianReifenscheid\CleanupTools\Service
 * @author Christian Reifenscheid
 */
class CleanFlexFormsService extends AbstractCleanupService
{

    /**
     * Setting start page in page tree.
     * Default is the page tree root, 0 (zero)
     *
     * @var int $pid
     */
    protected $pid;

    /**
     * Setting traversal depth.
     * 0 (zero) will only analyze start page (see --pid), 1 will traverse one level of subpages etc.
     *
     * @var int $depth
     */
    protected $depth;

    /**
     * Constructor
     */
    public function __construct(int $pid = 0, int $depth = 1000)
    {
        $this->pid = $pid;
        $this->depth = $depth;
    }

    /**
     * Find and update records with FlexForms where the values do not match the datastructures
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function execute(): \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        $startingPoint = MathUtility::forceIntegerInRange($this->pid, 0);
        $depth = MathUtility::forceIntegerInRange($this->depth, 0);

        // Find all records that should be updated
        $recordsToUpdate = $this->findAllDirtyFlexformsInPage($startingPoint, $depth);

        if ($this->dryRun) {
            $message = 'Found ' . count($recordsToUpdate) . ' records with wrong FlexForms information.';
            $this->addMessage($message);
            return $this->createFlashMessage(FlashMessage::INFO, $message);
        }

        if (! empty($recordsToUpdate)) {
            // Clean up the records now
            return $this->cleanFlexFormRecords($recordsToUpdate);
        } else {
            $message = 'Nothing to do - You\'re all set!';
            $this->addMessage($message);
            return $this->createFlashMessage(FlashMessage::OK, $message);
        }
    }

    /**
     * Execute for defined element
     *
     * @param int $recordUid
     */
    public function executeByUid(int $recordUid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');

        $records = [];

        $queryBuilder->select('*')
            ->from('tt_content')
            ->where($queryBuilder->expr()
            ->isNotNull('pi_flexform'))
            ->andWhere($queryBuilder->expr()
            ->eq('uid', $queryBuilder->createNamedParameter($recordUid, PDO::PARAM_INT)));

        $records['tt_content:' . $recordUid . ':pi_flexform'] = $queryBuilder->execute()->fetch();

        return $this->cleanFlexFormRecords($records);
    }

    /**
     * Validate given data
     *
     * @param array $data
     */
    public function isValid(array $data): bool
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        $cleanFlexForm = $flexObj->cleanFlexFormXML('tt_content', 'pi_flexform', $data);
        return ($cleanFlexForm === $data['pi_flexform']);
    }

    /**
     * Recursive traversal of page tree
     *
     * @param int $pageId
     *            Page root id
     * @param int $depth
     *            Depth
     * @param array $dirtyFlexFormFields
     *            the list of all previously found flexform fields
     *            
     * @return array
     */
    protected function findAllDirtyFlexformsInPage(int $pageId, int $depth, array $dirtyFlexFormFields = []): array
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
                    ->eq('pid', $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)))
                    ->execute();

                while ($rowSub = $result->fetch()) {
                    // Traverse flexforms
                    $dirtyFlexFormFields = $this->compareAllFlexFormsInRecord($tableName, $rowSub['uid'], $dirtyFlexFormFields);
                    // Add any versions of those records
                    $versions = BackendUtility::selectVersionsOfRecord($tableName, $rowSub['uid'], 'uid,t3ver_wsid,t3ver_count', null, true);
                    if (is_array($versions)) {
                        foreach ($versions as $verRec) {
                            if (! $verRec['_CURRENT_VERSION']) {
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
            $depth --;
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');

            $queryBuilder->getRestrictions()->removeAll();

            $result = $queryBuilder->select('uid')
                ->from('pages')
                ->where($queryBuilder->expr()
                ->eq('pid', $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)))
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
                    if (! $verRec['_CURRENT_VERSION']) {
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
     * @param string $tableName
     *            Table name
     * @param int $uid
     *            UID of record in processing
     * @param array $dirtyFlexFormFields
     *            the existing FlexForm fields
     *            
     * @return array the updated list of dirty FlexForm fields
     */
    protected function compareAllFlexFormsInRecord(string $tableName, int $uid, array $dirtyFlexFormFields = []): array
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        foreach ($GLOBALS['TCA'][$tableName]['columns'] as $columnName => $columnConfiguration) {
            if ($columnConfiguration['config']['type'] === 'flex') {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
                $queryBuilder->getRestrictions()->removeAll();

                $fullRecord = $queryBuilder->select('*')
                    ->from($tableName)
                    ->where($queryBuilder->expr()
                    ->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
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
     * @return FlashMessage
     */
    protected function cleanFlexFormRecords(array $records): FlashMessage
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);

        // Set up the data handler instance
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->dontProcessTransformations = true;
        $dataHandler->bypassWorkspaceRestrictions = true;
        $dataHandler->bypassFileHandling = true;
        // Setting this option allows to also update deleted records (or records on deleted pages) within DataHandler
        $dataHandler->bypassAccessCheckForRecords = true;

        // error counter
        $errors = 0;

        // Loop through all tables and their records
        foreach ($records as $recordIdentifier => $fullRecord) {
            list ($table, $uid, $field) = explode(':', $recordIdentifier);
            // Clean XML now
            $data = [];
            if ($fullRecord[$field]) {
                $data[$table][$uid][$field] = $flexObj->cleanFlexFormXML($table, $field, $fullRecord);
            } else {
                $this->addMessage('The field "' . $field . '" in record "' . $table . ':' . $uid . '" was not found.');
                continue;
            }
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            // Return errors if any:
            if (! empty($dataHandler->errorLog)) {
                $errorMessage = array_merge([
                    'DataHandler reported an error'
                ], $dataHandler->errorLog);
                $this->addMessage($errorMessage);
                $errors ++;
            } else {
                $this->addMessage('Updated FlexForm in record "' . $table . ':' . $uid . '".');
            }
        }

        if ($errors > 0) {
            $message = 'While executing ' . __CLASS__ . ' ' . $errors . ' occured.';
            return $this->createFlashMessage(FlashMessage::WARNING, $message);
        }

        return $this->createFlashMessage();
    }
}
