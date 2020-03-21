<?php
declare(strict_types = 1);
namespace SPL\SplCleanupTools\Service;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

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
 * Class FilesWithMultipleReferencesService
 * Finds files within uploads/ which are used multiple times by relations within the database
 * Originally taken from: \TYPO3\CMS\Lowlevel\Command\FilesWithMultipleReferencesCommand::class
 *
 * @package SPL\SplCleanupTools\Service
 * @author Christian Reifenscheid
 */
class FilesWithMultipleReferencesService
{
    /**
     * Executes the command to
     * - optionally update the reference index (to have clean data)
     * - find files within the reference index which are referenced more than once
     * - copy these files if --dry-run is not set and update the references accordingly
     *
     * @param bool $updateRefindex
     * @param bool $dryRun
     */
    protected function execute(bool $updateRefindex = false, bool $dryRun = true)
    {
        if ($updateRefindex) {
            $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
            $referenceIndex->updateIndex(false, false);
        }

        // Find files which are referenced multiple times
        $doubleFiles = $this->findMultipleReferencedFiles();
        
        if ($dryRun) {
            return count($doubleFiles);
        }

        else if (count($doubleFiles)) {
            $this->copyMultipleReferencedFiles($doubleFiles);
            // ToDo:Ausgabe Cleaned up ' . count($doubleFiles) . ' files which have been referenced multiple times.'
        } else {
            // ToDo:Ausgabe  'Nothing to do, no files found which are referenced more than once.'
        }
    }

    /**
     * Find files which are referenced multiple times in uploads/ folder
     *
     * @return array an array of files and their reference hashes that are referenced multiple times
     */
    protected function findMultipleReferencedFiles(): array
    {
        $multipleReferencesList = [];

        // Select all files in the reference table not found by a soft reference parser (thus TCA configured)
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_refindex');

        $result = $queryBuilder
            ->select('*')
            ->from('sys_refindex')
            ->where(
                $queryBuilder->expr()->eq('ref_table', $queryBuilder->createNamedParameter('_FILE', \PDO::PARAM_STR)),
                $queryBuilder->expr()->eq('softref_key', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
            )
            ->execute();

        // Traverse the files and put into a large table
        $allReferencesToFiles = [];
        while ($record = $result->fetch()) {
            // Compile info string for location of reference
            $infoString = $this->formatReferenceIndexEntryToString($record);
            $hash = $record['hash'];
            $fileName = $record['ref_string'];
            // Add entry if file has multiple references pointing to it
            if (isset($allReferencesToFiles[$fileName])) {
                if (!is_array($multipleReferencesList[$fileName])) {
                    $multipleReferencesList[$fileName] = [];
                    $multipleReferencesList[$fileName][$allReferencesToFiles[$fileName]['hash']] = $allReferencesToFiles[$fileName]['infoString'];
                }
                $multipleReferencesList[$fileName][$hash] = $infoString;
            } else {
                $allReferencesToFiles[$fileName] = [
                    'infoString' => $infoString,
                    'hash' => $hash
                ];
            }
        }

        return ArrayUtility::sortByKeyRecursive($multipleReferencesList);
    }

    /**
     * Copies files which are referenced multiple times and updates the reference index so they are only used once
     *
     * @param array $multipleReferencesToFiles Contains files which have been referenced multiple times
     */
    protected function copyMultipleReferencedFiles(array $multipleReferencesToFiles)
    {
        $fileFunc = GeneralUtility::makeInstance(BasicFileUtility::class);
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);

        foreach ($multipleReferencesToFiles as $fileName => $usages) {
            $absoluteFileName = GeneralUtility::getFileAbsFileName($fileName);
            if ($absoluteFileName && @is_file($absoluteFileName)) {
                $counter = 0;
                foreach ($usages as $hash => $recReference) {
                    if ($counter++ !== 0) {
                        // Create unique name for file
                        $newName = $fileFunc->getUniqueName(PathUtility::basename($fileName), PathUtility::dirname($absoluteFileName));
                        GeneralUtility::upload_copy_move($absoluteFileName, $newName);
                        clearstatcache();
                        if (@is_file($newName)) {
                            $error = $referenceIndex->setReferenceValue($hash, PathUtility::basename($newName));
                            if ($error) {
                                // ToDo:Ausgabe 'ReferenceIndex::setReferenceValue() reported "' . $error . '"'
                            }
                        } else {
                            // ToDo:Ausgabe 'File "' . $newName . '" could not be created.'
                        }
                    }
                }
            } else {
                // ToDo:Ausgabe 'File "' . $absoluteFileName . '" was not found.'
            }
        }
    }

    /**
     * Formats a sys_refindex entry to something readable
     *
     * @param array $record
     * @return string
     */
    protected function formatReferenceIndexEntryToString(array $record): string
    {
        return $record['tablename']
            . ':' . $record['recuid']
            . ':' . $record['field']
            . ($record['flexpointer'] ? ':' . $record['flexpointer'] : '')
            . ($record['softref_key'] ? ':' . $record['softref_key'] . ' (Soft Reference) ' : '')
            . ($record['deleted'] ? ' (DELETED)' : '');
    }
}
