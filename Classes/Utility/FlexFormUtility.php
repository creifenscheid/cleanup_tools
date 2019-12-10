<?php

namespace SPL\SplCleanupTools\Utility;

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
 * Class FlexFormUtility
 *
 * @package SPL\SplCleanupTools\Utility
 * @author  Christian Reifenscheid
 */
class FlexFormUtility
{   
    /**
     * Cleanup flexforms
     * 
     * @param null|int $recordUid
     * @return bool
     */
    public static function cleanupFlexForms ($recordUid = null) : bool
    {
        $table = 'tt_content';
        $fieldName = 'pi_flexform';
        
        // initialize flexform tools
        /** @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools $flexObj */
        $flexFormTools = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance (\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class);
        
        // initialize query builder
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $queryBuilder */
        $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance (\TYPO3\CMS\Core\Database\ConnectionPool::class)
        ->getQueryBuilderForTable ($table);
        
        // remove all restrictions like hidden, deleted etc.
        $queryBuilder->getRestrictions ()->removeAll ();
        
        // get full record of updated record
        $fullRecord = $queryBuilder->select ('*')
        ->from ($table)
        ->where (
            $queryBuilder->expr ()->eq ('uid', $queryBuilder->createNamedParameter ($recordUid, \PDO::PARAM_INT))
            )
            ->execute ()
            ->fetch ();
            
            // check if the defined field exists in the record
            if ($fullRecord[$fieldName]) {
                
                // clean XML and check against the record fetched from the database
                $cleanedFlexFormXML = $flexFormTools->cleanFlexFormXML ($table, $fieldName, $fullRecord);
                
                if ($cleanedFlexFormXML !== $fullRecord[$fieldName]) {
                    // update record with cleaned flexform
                    $result = $queryBuilder
                    ->update ($table)
                    ->where (
                        $queryBuilder->expr ()->eq ('uid', $queryBuilder->createNamedParameter ($recordUid))
                        )
                        ->set ($fieldName, $cleanedFlexFormXML)
                        ->execute ();
                        
                        // if something went wrong, drop a warning
                        if (!$result) {
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
        
        return true;
    }
    
    /**
     * Check if flexform of given record is valid
     * 
     * @param int $recordUid
     * @return bool
     */
    public static function isValidFlexForm (int $recordUid) : bool
    {
        // ToDo: add logic to check if flexform is well formed
        
        return false;
    }
}
