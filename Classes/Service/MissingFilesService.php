<?php

declare(strict_types=1);
namespace ChristianReifenscheid\CleanupTools\Service;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Backend\Command\ProgressListener\ReferenceIndexProgressListener;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Class MissingFilesService
 * Find all file references from records pointing to a missing (non-existing) file
 *
 * @see \TYPO3\CMS\Lowlevel\Command\MissingFilesCommand::class
 *
 * @package ChristianReifenscheid\CleanupTools\Service
 * @author Christian Reifenscheid
 */
class MissingFilesService extends AbstractCleanupService
{
    /**
     * Setting this option automatically updates the reference index
     *
     * @var bool
     */
    protected $updateRefindex = false;
    
    /**
     * @param boolean $updateRefindex
     * @return void
     */
    public function setUpdateRefindex(bool $updateRefindex) : void
    {
        $this->updateRefindex = $updateRefindex;
    }

    /**
     * Executes the command to
     * - optionally update the reference index (to have clean data)
     * - find data in sys_refindex (softrefs and regular references) where the actual file does not exist (anymore)
     * - remove these files if --dry-run is not set (not possible for refindexes)
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function execute(): \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        if ($this->updateRefIndex) {
            $this->updateReferenceIndex();
        }
        
        // message storage
        $messages = [];

        // Find missing soft references (cannot be updated / deleted)
        $missingSoftReferencedFiles = $this->findMissingSoftReferencedFiles();
        if (count($missingSoftReferencedFiles)) {
            $missingSoftReferencedFilesMessage = 'Found ' . count($missingSoftReferencedFiles) . ' soft-referenced files that need manual repair.';
            
            $messages[] = $missingSoftReferencedFilesMessage
            
            $this->addMessage($missingSoftReferencedFilesMessage);
        }

        // Find missing references
        $missingReferencedFiles = $this->findMissingReferencedFiles();
        if (count($missingReferencedFiles)) {
            $missingReferencedFilesMessage = 'Found ' . count($missingReferencedFiles) . ' references to non-existing files.';
            $messages[] = $missingReferencedFilesMessage;
            
            $this->addMessage($missingReferencedFilesMessage);

            if (!$this->dryRun) {
                return $this->removeReferencesToMissingFiles($missingReferencedFiles);
            }
            
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::INFO, implode(' ', $messages);
        }

        if (!count($missingSoftReferencedFiles) && !count($missingReferencedFiles)) {
            $message = 'Nothing to do, no missing files found. Everything is in place.';
            
            $this->addMessage($message);
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::OK, $message);
        }
    }

    /**
     * Function to update the reference index
     * - if the option --update-refindex is set, do it
     * - otherwise, if in interactive mode (not having -n set), ask the user
     * - otherwise assume everything is fine
     */
    protected function updateReferenceIndex()
    {
        $referenceIndex = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ReferenceIndex::class);
        $referenceIndex->updateIndex(false);
    }

    /**
     * Find file references that points to non-existing files in system
     * Fix methods: API in \TYPO3\CMS\Core\Database\ReferenceIndex that allows to
     * change the value of a reference (or remove it)
     *
     * @return array an array of records within sys_refindex
     */
    protected function findMissingReferencedFiles(): array
    {
        $missingReferences = [];
        // Select all files in the reference table
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_refindex');

        $result = $queryBuilder
            ->select('*')
            ->from('sys_refindex')
            ->where(
                $queryBuilder->expr()->eq('ref_table', $queryBuilder->createNamedParameter('_FILE', \PDO::PARAM_STR)),
                $queryBuilder->expr()->isNull('softref_key')
            )
            ->execute();

        // Traverse the references and check if the files exists
        while ($record = $result->fetch()) {
            $fileName = $this->getFileNameWithoutAnchor($record['ref_string']);
            if (empty($record['softref_key']) && !@is_file(Environment::getPublicPath() . '/' . $fileName)) {
                $missingReferences[$fileName][$record['hash']] = $this->formatReferenceIndexEntryToString($record);
            }
        }

        return $missingReferences;
    }

    /**
     * Find file references that points to non-existing files in system
     * registered as soft references (checked for "softref_key")
     *
     * @return array an array of the data within soft references
     */
    protected function findMissingSoftReferencedFiles(): array
    {
        $missingReferences = [];
        // Select all files in the reference table
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_refindex');

        $result = $queryBuilder
            ->select('*')
            ->from('sys_refindex')
            ->where(
                $queryBuilder->expr()->eq('ref_table', $queryBuilder->createNamedParameter('_FILE', \PDO::PARAM_STR)),
                $queryBuilder->expr()->isNotNull('softref_key')
            )
            ->execute();

        // Traverse the references and check if the files exists
        while ($record = $result->fetch()) {
            $fileName = $this->getFileNameWithoutAnchor($record['ref_string']);
            if (!@is_file(Environment::getPublicPath() . '/' . $fileName)) {
                $missingReferences[] = $fileName . ' - ' . $record['hash'] . ' - ' . $this->formatReferenceIndexEntryToString($record);
            }
        }
        return $missingReferences;
    }

    /**
     * Remove a possible anchor like 'my-path/file.pdf#page15'
     *
     * @param string $fileName a filename as found in sys_refindex.ref_string
     * @return string the filename but leaving everything behind #page15 behind
     */
    protected function getFileNameWithoutAnchor(string $fileName): string
    {
        if (strpos($fileName, '#') !== false) {
            [$fileName] = explode('#', $fileName);
        }
        return $fileName;
    }

    /**
     * Removes all references in the sys_file_reference where files were not found
     *
     * @param array $missingManagedFiles Contains the records of sys_refindex which need to be updated
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    protected function removeReferencesToMissingFiles(array $missingManagedFiles) : \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        $errors = false;
    
        foreach ($missingManagedFiles as $fileName => $references) {
            foreach ($references as $hash => $recordReference) {
                $sysRefObj = GeneralUtility::makeInstance(ReferenceIndex::class);
                $error = $sysRefObj->setReferenceValue($hash, null);
                if ($error) {
                    $errors = true;
                    $message = 'ReferenceIndex::setReferenceValue() reported "' . $error . '"';
                    $this->addMessage($message);
                }
            }
        }
        
        if ($error) {
            $message = 'Errors occurred while updating references. See log for further information';
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::WARNING, $message);
        }
        
        $message = 'All references were updated accordingly.';
        return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::OK, $message);
    }

    /**
     * Formats a sys_refindex entry to something readable
     *
     * @param array $record
     * @return string
     */
    protected function formatReferenceIndexEntryToString(array $record): string
    {
        return $record['tablename'] . ':' . $record['recuid'] . ':' . $record['field'] . ':' . $record['flexpointer'] . ':' . $record['softref_key'] . ($record['deleted'] ? ' (DELETED)' : '');
    }
}