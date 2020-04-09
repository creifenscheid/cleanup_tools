<?php
declare(strict_types = 1);
namespace SPL\SplCleanupTools\Service;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

*************************************************************
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
 * Class RteImagesService
 * Looking up all occurencies of RTEmagic images in the database and check existence of parent and
 * copy files on the file system plus report possibly lost files of this type
 * @see \TYPO3\CMS\Lowlevel\Command\RteImagesCommand::class
 *
 * @package SPL\SplCleanupTools\Service
 * @author Christian Reifenscheid
 */
class RteImagesService extends AbstractCleanupService
{
    /**
     * Setting this option automatically updates the reference index
     * 
     * @var bool
     */
     protected $updateRefindex = false;
     
    /**
     * Executes the command to
     * - optionally update the reference index (to have clean data)
     * - find files within uploads/* which are not connected to the reference index
     * - remove these files if dryRun is not set
     */
    protected function execute()
    {
        if ($this->updateRefindex) {
            $this->updateReferenceIndex();
        }

        // Find the RTE files
        $allRteImagesInUse = $this->findAllReferencedRteImagesWithOriginals();

        if (count($allRteImagesInUse)) {
            $allRteImagesWithOriginals = [];
            $multipleReferenced = [];
            $missingFiles = [];
            $lostFiles = [];

            // Searching for duplicates, and missing files (also missing originals)
            foreach ($allRteImagesInUse as $fileName => $fileInfo) {
                $allRteImagesWithOriginals[$fileName]++;
                $allRteImagesWithOriginals[$fileInfo['original']]++;
                if ($fileInfo['count'] > 1 && $fileInfo['exists'] && $fileInfo['original_exists']) {
                    $multipleReferenced[$fileName] = $fileInfo['softReferences'];
                }
                // Missing files:
                if (!$fileInfo['exists']) {
                    $missingFiles[$fileName] = $fileInfo['softReferences'];
                }
                if (!$fileInfo['original_exists']) {
                    $missingFiles[$fileInfo['original']] = $fileInfo['softReferences'];
                }
            }

            // Now, ask for RTEmagic files inside uploads/ folder:
            $magicFiles = $this->findAllRteFilesInDirectory();
            foreach ($magicFiles as $fileName) {
                if (!isset($allRteImagesWithOriginals[$fileName])) {
                    $lostFiles[$fileName] = $fileName;
                }
            }
            ksort($missingFiles);
            ksort($multipleReferenced);
            
            if ($this->dryRun) {
                $message = 'Found ' . count($missingFiles) . ' RTE images that are referenced, but missing.';
                $this->addMessage($message);
                return $message;
            } else {
                // Duplicate RTEmagic image files
                // These files are RTEmagic images found used in multiple records! RTEmagic images should be used by only
                // one record at a time. A large amount of such images probably stems from previous versions of TYPO3 (before 4.2)
                // which did not support making copies automatically of RTEmagic images in case of new copies / versions.
                $this->copyMultipleReferencedRteImages($multipleReferenced);

                // Delete lost files
                // Lost RTEmagic files from uploads/
                // These files you might be able to delete but only if _all_ RTEmagic images are found by the soft reference parser.
                // If you are using the RTE in third-party extensions it is likely that the soft reference parser is not applied
                // correctly to their RTE and thus these "lost" files actually represent valid RTEmagic images,
                // just not registered. Lost files can be auto-fixed but only if you specifically
                // set "lostFiles" as parameter to the --AUTOFIX option.
                if (count($lostFiles)) {
                    ksort($lostFiles);
                    return $this->deleteLostFiles($lostFiles);
                }
                
                return true;
            }
        } else {
            return 'Nothing to do, your system does not have any RTE images.';
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
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
        $referenceIndex->updateIndex(false, !$io->isQuiet());
    }

    /**
     * Find lost files in uploads/ folder
     *
     * @return array an array of files (relative to Environment::getPublicPath()) that are not connected
     */
    protected function findAllReferencedRteImagesWithOriginals(): array
    {
        $allRteImagesInUse = [];

        // Select all RTEmagic files in the reference table (only from soft references of course)
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_refindex');

        $result = $queryBuilder
            ->select('*')
            ->from('sys_refindex')
            ->where(
                $queryBuilder->expr()->eq(
                    'ref_table',
                    $queryBuilder->createNamedParameter('_FILE', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->like(
                    'ref_string',
                    $queryBuilder->createNamedParameter('%/RTEmagic%', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'softref_key',
                    $queryBuilder->createNamedParameter('images', \PDO::PARAM_STR)
                )
            )
            ->execute();

        // Traverse the files and put into a large table:
        while ($rec = $result->fetch()) {
            $file = $rec['ref_string'];
            $filename = PathUtility::basenameDuringBootstrap($file);
            if (strpos($filename, 'RTEmagicC_') === 0) {
                // First time the file is referenced => build index
                if (!is_array($allRteImagesInUse[$file])) {
                    $original = 'RTEmagicP_' . preg_replace('/\\.[[:alnum:]]+$/', '', substr($filename, 10));
                    $original = substr($file, 0, -strlen($filename)) . $original;
                    $allRteImagesInUse[$file] = [
                        'exists' => @is_file(Environment::getPublicPath() . '/' . $file),
                        'original' => $original,
                        'original_exists' => @is_file(Environment::getPublicPath() . '/' . $original),
                        'count' => 0,
                        'softReferences' => []
                    ];
                }
                $allRteImagesInUse[$file]['count']++;
                $allRteImagesInUse[$file]['softReferences'][$rec['hash']] = $this->formatReferenceIndexEntryToString($rec);
            }
        }

        ksort($allRteImagesInUse);
        return $allRteImagesInUse;
    }

    /**
     * Find all RTE files in uploads/ folder
     *
     * @param string $folder the name of the folder to start from
     * @return array an array of files (relative to Environment::getPublicPath()) that are not connected
     */
    protected function findAllRteFilesInDirectory($folder = 'uploads/'): array
    {
        $filesFound = [];

        // Get all files
        $files = [];
        $files = GeneralUtility::getAllFilesAndFoldersInPath($files, Environment::getPublicPath() . '/' . $folder);
        $files = GeneralUtility::removePrefixPathFromList($files, Environment::getPublicPath() . '/');

        // Traverse files
        foreach ($files as $key => $value) {
            // If the file is a RTEmagic-image name
            if (preg_match('/^RTEmagic[P|C]_/', PathUtility::basenameDuringBootstrap($value))) {
                $filesFound[] = $value;
                continue;
            }
        }

        return $filesFound;
    }

    /**
     * Removes given files from the uploads/ folder
     *
     * @param array $lostFiles Contains the lost files found
     * @param bool $dryRun if set, the files are just displayed, but not deleted
     * @param SymfonyStyle $io the IO object for output
     */
    protected function deleteLostFiles(array $lostFiles
    {
        foreach ($lostFiles as $lostFile) {
            $absoluteFileName = GeneralUtility::getFileAbsFileName($lostFile);
            if ($io->isVeryVerbose()) {
                $io->writeln('Deleting file "' . $absoluteFileName . '"');
            }
            if ($absoluteFileName && @is_file($absoluteFileName)) {
                unlink($absoluteFileName);
                $this->addMessage('Permanently deleted file "' . $absoluteFileName . '".');
            } else {
                $this->addMessage('File "' . $absoluteFileName . '" was not found!');
            }
            
            return true;
        }
    }

    /**
     * Duplicate RTEmagic image files which are used on several records. RTEmagic images should be used by only
     * one record at a time. A large amount of such images probably stems from previous versions of TYPO3 (before 4.2)
     * which did not support making copies automatically of RTEmagic images in case of new copies / versions.
     *
     * @param array $multipleReferencedImages
     * @param bool $dryRun
     * @param SymfonyStyle $io
     */
    protected function copyMultipleReferencedRteImages(array $multipleReferencedImages)
    {
        $fileProcObj = GeneralUtility::makeInstance(BasicFileUtility::class);
        foreach ($multipleReferencedImages as $fileName => $fileInfo) {
            // Traverse all records using the file
            $c = 0;
            foreach ($fileInfo['usedIn'] as $hash => $recordID) {
                if ($c === 0) {
                    $this->addMessage('Keeping file ' . $fileName . ' for record ' . $recordID);
                } else {
                    $this->addMessage('Copying file ' . PathUtility::basenameDuringBootstrap($fileName) . ' for record ' . $recordID);
                    // Get directory prefix for file and set the original name
                    $dirPrefix = PathUtility::dirnameDuringBootstrap($fileName) . '/';
                    $rteOrigName = PathUtility::basenameDuringBootstrap($fileInfo['original']);
                    // If filename looks like an RTE file, and the directory is in "uploads/", then process as a RTE file!
                    if ($rteOrigName && strpos($dirPrefix, 'uploads/') === 0 && @is_dir(Environment::getPublicPath() . '/' . $dirPrefix)) {
                        // From the "original" RTE filename, produce a new "original" destination filename which is unused.
                        $origDestName = $fileProcObj->getUniqueName($rteOrigName, Environment::getPublicPath() . '/' . $dirPrefix);
                        // Create copy file name
                        $pI = pathinfo($fileName);
                        $copyDestName = PathUtility::dirnameDuringBootstrap($origDestName) . '/RTEmagicC_' . substr(PathUtility::basenameDuringBootstrap($origDestName), 10) . '.' . $pI['extension'];
                        if (!@is_file($copyDestName) && !@is_file($origDestName) && $origDestName === GeneralUtility::getFileAbsFileName($origDestName) && $copyDestName === GeneralUtility::getFileAbsFileName($copyDestName)) {
                            $this->addMessage('Copying file ' . PathUtility::basenameDuringBootstrap($fileName) . ' for record ' . $recordID . ' to ' . PathUtility::basenameDuringBootstrap($copyDestName));
                            if (!$dryRun) {
                                // Making copies
                                GeneralUtility::upload_copy_move(Environment::getPublicPath() . '/' . $fileInfo['original'], $origDestName);
                                GeneralUtility::upload_copy_move(Environment::getPublicPath() . '/' . $fileName, $copyDestName);
                                clearstatcache();
                                if (@is_file($copyDestName)) {
                                    $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
                                    $error = $referenceIndex->setReferenceValue($hash, PathUtility::stripPathSitePrefix($copyDestName));
                                    if ($error) {
                                        $io->error('ReferenceIndex::setReferenceValue() reported "' . $error . '"');
                                    }
                                } else {
                                    $io->error('File "' . $copyDestName . '" could not be created.');
                                }
                            }
                        } else {
                            $io->error('Could not construct new unique names for file.');
                        }
                    } else {
                        $io->error('Maybe directory of file was not within "uploads/"?');
                    }
                }
                $c++;
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