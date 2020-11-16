<?php
declare(strict_types = 1);
namespace CReifenscheid\CleanupTools\Service;

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
 * Class LostFilesService
 * Finds files within uploads/ which are not needed anymore
 *
 * @see \TYPO3\CMS\Lowlevel\Command\LostFilesCommand::class
 *
 * @package CReifenscheid\CleanupTools\Service
 * @author C. Reifenscheid
 */
class LostFilesService extends AbstractCleanupService
{

    /**
     * Comma-separated list of paths that should be excluded, e.g.
     * "uploads/pics,uploads/media"
     *
     * @var string
     */
    protected $exclude = '';

    /**
     * Comma separated list of paths to process.
     * Example: "fileadmin/[path1],fileadmin/[path2],...", if not passed, uploads/ will be used by default.
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
     * @param string $exclude
     * @return void
     */
    public function setExclude(string $exclude) : void
    {
        $this->exclude = $exclude;
    }

    /**
     * @param string $customPath
     * @return void
     */
    public function setCustomPath(string $customPath) : void
    {
        $this->customPath = $customPath;
    }

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
     * - find files within uploads/* which are not connected to the reference index
     * - remove these files if --dry-run is not set
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function execute(): \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        if ($this->updateRefIndex) {
            $this->updateReferenceIndex();
        }

        // Find the lost files
        if (! empty($this->exclude)) {
            $excludedPaths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->exclude, true);
        } else {
            $excludedPaths = [];
        }

        // Use custom-path
        if (! empty($this->customPath)) {
            $customPaths = $this->customPath;
        }

        $lostFiles = $this->findLostFiles($excludedPaths, $customPaths);

        if (count($lostFiles)) {
            if ($this->dryRun) {
                $message = 'Found ' . count($lostFiles) . ' lost files, ready to be deleted.';
                $this->addMessage($message);
                return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::INFO, $message);
            } else {
                // Delete them
                return $this->deleteLostFiles($lostFiles);
            }
        } else {
            $message = 'Nothing to do, no lost files found';
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
     * Find lost files in uploads/ or custom folder
     *
     * @param array $excludedPaths
     *            list of paths to be excluded, can be uploads/pics/
     * @param string $customPaths
     *            list of paths to be checked instead of uploads/
     * @return array an array of files (relative to Environment::getPublicPath()) that are not connected
     */
    protected function findLostFiles($excludedPaths = [], $customPaths = ''): array
    {
        $lostFiles = [];

        // Get all files
        $files = [];
        if (! empty($customPaths)) {
            $customPaths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $customPaths, true);
            foreach ($customPaths as $customPath) {
                if (false === realpath(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . $customPath) || ! \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr(realpath(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . $customPath), realpath(\TYPO3\CMS\Core\Core\Environment::getPublicPath()))) {
                    throw new \Exception('The path: "' . $customPath . '" is invalid', 1450086736);
                }
                $files = \TYPO3\CMS\Core\Utility\GeneralUtility::getAllFilesAndFoldersInPath($files, \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . $customPath);
            }
        } else {
            $files = \TYPO3\CMS\Core\Utility\GeneralUtility::getAllFilesAndFoldersInPath($files, \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/uploads/');
        }

        $files = \TYPO3\CMS\Core\Utility\GeneralUtility::removePrefixPathFromList($files, \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/');

        $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('sys_refindex');

        // Traverse files and for each, look up if its found in the reference index.
        foreach ($files as $key => $value) {

            // First, allow "index.html", ".htaccess" files since they are often used for good reasons
            if (substr($value, - 11) === '/index.html' || substr($value, - 10) === '/.htaccess') {
                continue;
            }

            // If the file is a RTEmagic-image name and if so, we allow it
            if (preg_match('/^RTEmagic[P|C]_/', \TYPO3\CMS\Core\Utility\PathUtility::basenameDuringBootstrap($value))) {
                continue;
            }

            $fileIsInExcludedPath = false;
            foreach ($excludedPaths as $exclPath) {
                if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($value, $exclPath)) {
                    $fileIsInExcludedPath = true;
                    break;
                }
            }

            if ($fileIsInExcludedPath) {
                continue;
            }

            // Looking for a reference from a field which is NOT a soft reference (thus, only fields with a proper TCA/Flexform configuration)
            $queryBuilder->select('hash')
                ->from('sys_refindex')
                ->where($queryBuilder->expr()
                ->eq('ref_table', $queryBuilder->createNamedParameter('_FILE', \PDO::PARAM_STR)), $queryBuilder->expr()
                ->eq('ref_string', $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)), $queryBuilder->expr()
                ->eq('softref_key', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)))
                ->orderBy('sorting', 'DESC')
                ->execute();

            $rowCount = $queryBuilder->count('hash')
                ->execute()
                ->fetchColumn(0);
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
     * @param array $lostFiles
     *            Contains the lost files found
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    protected function deleteLostFiles(array $lostFiles) : \TYPO3\CMS\Core\Messaging\FlashMessage
    {
        // error counter
        $errors = 0;

        foreach ($lostFiles as $lostFile) {
            $absoluteFileName = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($lostFile);

            if ($absoluteFileName && @is_file($absoluteFileName)) {
                unlink($absoluteFileName);
                $this->addMessage('Permanently deleted file record "' . $absoluteFileName . '".');
            } else {
                $this->addMessage('File "' . $absoluteFileName . '" was not found!');
                $errors ++;
            }
        }

        if ($errors > 0) {
            $message = 'While executing ' . __CLASS__ . ' ' . $errors . ' occured.';
            return $this->createFlashMessage(\TYPO3\CMS\Core\Messaging\FlashMessage::WARNING, $message);
        }

        return $this->createFlashMessage();
    }
}