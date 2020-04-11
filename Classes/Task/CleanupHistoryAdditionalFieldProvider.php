<?php

namespace SPL\SplCleanupTools\Task;

use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
 * Class CleanupHistoryAdditionalFieldProvider
 *
 * @package SPL\SplCleanupTools\Task
 * @author  Christian Reifenscheid
 */
class CleanupHistoryAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Drop already deleted
     *
     * @var bool
     */
    protected $dropAlreadyDeleted = true;
    
    /**
     * Task name
     *
     * @var string
     */
    protected $taskName = 'scheduler_cleanuphistory';
    
    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array                                                     $taskInfo        Values of the fields from the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask                    $task            The task object being edited. Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $currentSchedulerModuleMethod = $schedulerModule->getCurrentAction();
        
        $additionalFields = [];
        
        // Initialize selected fields
        if (!isset($taskInfo[$this->taskName])) {
            $taskInfo[$this->taskName] = $this->dropAlreadyDeleted;
            if ($currentSchedulerModuleMethod->equals(Action::EDIT)) {
                $taskInfo[$this->taskName] = $task->dropAlreadyDeleted();
            }
        }
        
        $fieldName = 'tx_scheduler[' . $this->taskName . ']';
        $fieldValue = $taskInfo[$this->taskName];
        $fieldHtml = $this->buildResourceSelector($fieldName, $this->taskName, $fieldValue);
        $additionalFields[$this->taskName] = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanuphistory.fields.dropAlreadyDeletef',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $this->taskName
        ];
        
        return $additionalFields;
    }
    
    /**
     * Validates the additional fields' values
     *
     * @param array                                                     $submittedData   An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule) : bool
    {
        return true;
    }
    
    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array                                  $submittedData An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task          Reference to the scheduler backend module
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->dropAlreadyDeleted($submittedData[$this->taskName]);
    }
    
    /**
     * Build select field with configured services
     * 
     * @param $fieldName
     * @param $fieldId
     * @param $fieldValue
     *
     * @return string
     */
    private function buildResourceSelector($fieldName, $fieldId, $fieldValue) : string
    {
        $options = '<option value="1">' . LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:label.yes', 'SplCleanupTools') . '</option>';
        
        $options .= '<option value="0">' . LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:label.no', 'SplCleanupTools') . '</option>';
        
        return '<select class="form-control" name="' . $fieldName . '" id="' . $fieldId . '">'.$options.'</select>';
    }
}
