<?php
declare(strict_types = 1);
namespace CReifenscheid\CleanupTools\Service\CleanupService;

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
 * Finds files within uploads/ which are used multiple times by relations within the database
 *
 * @see \TYPO3\CMS\Lowlevel\Command\FilesWithMultipleReferencesCommand::class
 *
 * @package CReifenscheid\CleanupTools\Service\CleanupService
 * @author C. Reifenscheid
 */
class FilesWithMultipleReferencesService extends AbstractCleanupService
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
     * - find files within the reference index which are referenced more than once
     * - copy these files if --dry-run is not set and update the references accordingly
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function execute(): \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        // Update the reference index
        if ($this->updateRefIndex) {
            $this->updateReferenceIndex();
        }

        // Find files which are referenced multiple times
        $doubleFiles = $this->findMultipleReferencedFiles();

        if (count($doubleFiles)) {
        
            if ($this->dryRun) {
            
                $message = 'Found ' . count($doubleFiles) . ' files that are referenced more than once.';

                $this->addMessage($message);
                return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::INFO, $message);
                
            } 
            
            $this->copyMultipleReferencedFiles($doubleFiles);
            $message = 'Cleaned up ' . count($doubleFiles) . ' files which have been referenced multiple times.';

            $this->addMessage($message);
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::OK, $message);

        } else {
            $message = 'Nothing to do, no files found which are referenced more than once.';
            $this->addMessage($message);
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::INFO, $message);
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
                    if ($counter++ === 0) {
                        $this->addMessage('Keeping "' . $fileName . '" for record "' . $recReference . '"');
                    } else {
                        // Create unique name for file
                        $newName = (string)$fileFunc->getUniqueName(PathUtility::basename($fileName), PathUtility::dirname($absoluteFileName));
                        $this->addMessage('Copying "' . $fileName . '" to "' . PathUtility::stripPathSitePrefix($newName) . '" for record "' . $recReference . '"');
                        
                        GeneralUtility::upload_copy_move($absoluteFileName, $newName);
                        clearstatcache();
                        if (@is_file($newName)) {
                            $error = $referenceIndex->setReferenceValue($hash, PathUtility::basename($newName));
                            if ($error) {
                                    $this->addMessage('ReferenceIndex::setReferenceValue() reported "' . $error . '"');
                            }
                        } else {
                                $this->addMessage('File "' . $newName . '" could not be created.');
                        }
                    }
                }
            } else {
                $this->addMessage('File "' . $absoluteFileName . '" was not found.');
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
