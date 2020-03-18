<?php

namespace SPL\SplCleanupTools\Service;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class FlexFormService
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class FlexFormService
{   
    /**
     * Flexform tools
     *
     * @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools
     */
    protected $flexformTools;
    
    /**
     * Connection
     *
     * @var \TYPO3\CMS\Core\Database\ConnectionPool
     */
    protected $connection;
    
    /**
     * table
     *
     * @var string
     */
    protected $table = 'tt_content';
    
    /**
     * field name
     *
     * @var string
     */
    protected $fieldName = 'pi_flexform';
    
    /**
     * __construct
     */
    public function __construct () {
        // initialize flexform tools
        $this->flexformTools = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance (\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class);
        
        // initialize connection
        $this->connection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance (\TYPO3\CMS\Core\Database\ConnectionPool::class);
    }
    
    /**
     * Cleanup flexforms
     * 
     * @param null|int $recordUid
     * @return bool
     */
    public function cleanupFlexForms ($recordUid = null) : bool
    {
        // define return flag
        $return = true;

        // init new querybuilder
        $queryBuilder = $this->connection->getQueryBuilderForTable ($this->table);
        
        // set up querybuilder
        $queryBuilder->select ('*')
            ->from ($this->table);
        
        // if a record uid is given
        if ($recordUid) {
            $queryBuilder->where (
                $queryBuilder->expr ()->eq ('uid', $queryBuilder->createNamedParameter ($recordUid, \PDO::PARAM_INT))
            );
        }

        $records = $queryBuilder->execute ()->fetchAll ();
            
        // process records
        foreach ($records as $record) {

            // check field
            if ($record[$this->fieldName]) {

                // check if flexform is valid
                if (!$this->isValid($record)) {

                    // init new querybilder
                    $queryBuilder = $this->connection->getQueryBuilderForTable($this->table);

                    // update record
                    $result = $queryBuilder
                        ->update($this->table)
                        ->where(
                            $queryBuilder->expr ()->eq('uid', $queryBuilder->createNamedParameter ($record['uid']))
                        )
                        ->set($this->fieldName, $this->getCleanFlexform($record))
                        ->execute();

                    // if something went wrong, drop a warning
                    if (!$result) {

                        $return = false;

                        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $message */
                        $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance (\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'messages.hook.warning.message',
                                'spl_cleanup_tools'
                            ),
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'messages.hook.warning.headline',
                                'spl_cleanup_tools'
                            ),
                            \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING
                        );

                        /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
                        $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance (\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
                        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                        $messageQueue->addMessage($message);
                    }
                }
            }
        }

        return $return;
    }
    
    /**
     * Validate flexform of given record
     * 
     * @param array $record
     * @return bool
     */
    public function isValid (array $record) : bool
    {
        return ($this->getCleanFlexform($record) === $record[$this->fieldName]);
    }
    
    /**
     * Return cleaned flexform
     * 
     * @param array $record
     * @return string
     */
    private function getCleanFlexform (array $record) : string
    {
        return $this->flexformTools->cleanFlexFormXML ($this->table, $this->fieldName, $record);
    }
}
