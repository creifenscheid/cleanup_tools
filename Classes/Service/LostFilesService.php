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
 * Class LostFilesService
 * Finds files within uploads/ which are not needed anymore
 * @see \TYPO3\CMS\Lowlevel\Command\LostFilesCommand::class
 *
 * @package SPL\SplCleanupTools\Service
 * @author Christian Reifenscheid
 */
class LostFilesService extends AbstractCleanupService
{
    /**
     * Comma separated list of paths to exclude
     *
     * @var string
     */
    protected $exclude = '';
    
    /**
     * Comma separated list of paths to process. Example: "fileadmin/[path1],fileadmin/[path2],...", if not passed, uploads/ will be used by default.
     *
     * @var string
     */
    protected $customPath = '';
    
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
     * - remove these files if dryRun is false
     */
    protected function execute()
    {
        if ($this->updateRefindex) {
            $this->updateReferenceIndex();
        }

        // Find the lost files
        if (!empty($this->exclude)) {
            $excludedPaths = GeneralUtility::trimExplode(',', $this->exclude, true);
        } else {
            $excludedPaths = [];
        }

        // Use custom-path
        $customPaths = '';
        if (!empty($this->customPath)) {
            $customPaths = $this->customPath;
        }

        $lostFiles = $this->findLostFiles($excludedPaths, $customPaths);

        if (count($lostFiles)) {
        
            if ($this->dryRun) {
                return count($lostFiles);
            }

            // Delete them
            return $this->deleteLostFiles($lostFiles);
        } else {
            return 'Nothing to do, no lost files found';
        }
    }

    /**
     * Function to update the reference index
     * - if the option --update-refindex is set
     */
    protected function updateReferenceIndex()
    {
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
        $referenceIndex->updateIndex(false);
    }

    /**
     * Find lost files in uploads/ or custom folder
     *
     * @param array $excludedPaths list of paths to be excluded, can be uploads/pics/
     * @param string $customPaths list of paths to be checked instead of uploads/
     * @return array an array of files (relative to Environment::getPublicPath()) that are not connected
     */
    protected function findLostFiles($excludedPaths = [], $customPaths = ''): array
    {
        $lostFiles = [];

        // Get all files
        $files = [];
        if (!empty($customPaths)) {
            $customPaths = GeneralUtility::trimExplode(',', $customPaths, true);
            foreach ($customPaths as $customPath) {
                if (false === realpath(Environment::getPublicPath() . '/' . $customPath)
                    || !GeneralUtility::isFirstPartOfStr(realpath(Environment::getPublicPath() . '/' . $customPath), realpath(Environment::getPublicPath()))) {
                    $this->addMessage('The path: "' . $customPath . '" is invalid');
                }
                $files = GeneralUtility::getAllFilesAndFoldersInPath($files, Environment::getPublicPath() . '/' . $customPath);
            }
        } else {
            $files = GeneralUtility::getAllFilesAndFoldersInPath($files, Environment::getPublicPath() . '/uploads/');
        }

        $files = GeneralUtility::removePrefixPathFromList($files, Environment::getPublicPath() . '/');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_refindex');

        // Traverse files and for each, look up if its found in the reference index.
        foreach ($files as $key => $value) {

            // First, allow "index.html", ".htaccess" files since they are often used for good reasons
            if (substr($value, -11) === '/index.html' || substr($value, -10) === '/.htaccess') {
                continue;
            }

            // If the file is a RTEmagic-image name and if so, we allow it
            if (preg_match('/^RTEmagic[P|C]_/', PathUtility::basenameDuringBootstrap($value))) {
                continue;
            }

            $fileIsInExcludedPath = false;
            foreach ($excludedPaths as $exclPath) {
                if (GeneralUtility::isFirstPartOfStr($value, $exclPath)) {
                    $fileIsInExcludedPath = true;
                    break;
                }
            }

            if ($fileIsInExcludedPath) {
                continue;
            }

            // Looking for a reference from a field which is NOT a soft reference (thus, only fields with a proper TCA/Flexform configuration)
            $queryBuilder
                ->select('hash')
                ->from('sys_refindex')
                ->where(
                    $queryBuilder->expr()->eq(
                        'ref_table',
                        $queryBuilder->createNamedParameter('_FILE', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'ref_string',
                        $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'softref_key',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    )
                )
                ->orderBy('sorting', 'DESC')
                ->execute();

            $rowCount = $queryBuilder->count('hash')->execute()->fetchColumn(0);
            // We conclude that the file is lost
            if ($rowCount === 0) {
                $lostFiles[] = $value;
            }
        }

        return $lostFiles;
    }

    /**
     * Removes given files from the uploads/ folder
     *
     * @param array $lostFiles Contains the lost files found
     */
    protected function deleteLostFiles(array $lostFiles)
    {
        foreach ($lostFiles as $lostFile) {
            $absoluteFileName = GeneralUtility::getFileAbsFileName($lostFile);

            if ($absoluteFileName && @is_file($absoluteFileName)) {
                unlink($absoluteFileName);
            }
        }
        
        return true;
    }
}