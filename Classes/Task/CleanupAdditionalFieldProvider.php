<?php

namespace SPL\SplCleanupTools\Task;

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
 * Class CleanupController
 *
 * @package SPL\SplCleanupTools\Task
 * @author  Christian Reifenscheid
 */
class CleanupAdditionalFieldProvider extends \TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider
{
    /**
     * Cleanup action
     *
     * @var string
     */
    protected $cleanupAction = '';
    
    /**
     * Parameter
     * 
     * @var null|array
     */
    protected $parameter;
    
    /**
     * 
     * @var array
     */
    protected $currentMethod = [];

    /**
     * Configuration utility
     *
     * @var \SPL\SplCleanupTools\Utility\ConfigurationUtility $configurationUtility
     */
    protected $configurationUtility;
    
    /**
     * Task name
     *
     * @var string
     */
    protected $cleanupActionTaskName = 'scheduler_cleanuptools_cleanupaction';
    
    /**
     * Task name
     *
     * @var string
     */
    protected $parameterTaskName = 'scheduler_cleanuptools_parameter';
    
    /**
     * CleanupAdditionalFieldProvider constructor.
     */
    public function __construct()
    {
        // init configurationUtility
        $this->configurationUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\ConfigurationUtility::class);
    }

    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array                                                     $taskInfo        Values of the fields from the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask                    $task            The task object being edited. Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();
        
        $additionalFields = [];

        // Initialize selected fields
        // Cleanup action
        if (!isset($taskInfo[$this->cleanupActionTaskName])) {
            $taskInfo[$this->cleanupActionTaskName] = $this->cleanupAction;
            if ($currentSchedulerModuleAction->equals(\TYPO3\CMS\Scheduler\Task\Enumeration\Action::EDIT)) {
                $taskInfo[$this->cleanupActionTaskName] = $task->getCleanupAction();
                
                $this->currentMethod = $this->configurationUtility->getMethodConfiguration($task->getCleanupAction());
            }
        }
        
        $this->currentMethod = $this->configurationUtility->getMethodConfiguration($taskInfo[$this->cleanupActionTaskName]);
        
        $fieldName = 'tx_scheduler[' . $this->cleanupActionTaskName . ']';
        $fieldValue = $taskInfo[$this->cleanupActionTaskName];
        $fieldHtml = $this->buildResourceSelector($fieldName, $this->cleanupActionTaskName, $fieldValue);
        $additionalFields[$this->cleanupActionTaskName] = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.fields.cleanupaction',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $this->cleanupActionTaskName
        ];
        
        // Parameter
        if (!empty($this->currentMethod['parameters'])) {
            if (!isset($taskInfo[$this->parameterTaskName])) {
                $taskInfo[$this->parameterTaskName] = $this->parameter;
                if ($currentSchedulerModuleAction->equals(\TYPO3\CMS\Scheduler\Task\Enumeration\Action::EDIT)) {
                    $taskInfo[$this->parameterTaskName] = $task->getParameter();
                }
            }
            
            $fieldName = 'tx_scheduler[' . $this->parameterTaskName . ']';
            $fieldValue = $taskInfo[$this->parameterTaskName];
            $fieldHtml = '<div>Parameter</div>';
            $additionalFields[$this->parameterTaskName] = [
                'code' => $fieldHtml,
                'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.fields.parameter',
                'cshKey' => '_MOD_system_txschedulerM1',
                'cshLabel' => $this->parameterTaskName
            ];
        }
        
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
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) : bool
    {
        if ($this->configurationUtility->getUtilityByMethod($submittedData[$this->cleanupActionTaskName])) {
            return true;
        }

        return false;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array                                  $submittedData An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task          Reference to the scheduler backend module
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        $task->setCleanupAction($submittedData[$this->cleanupActionTaskName]);
        $task->setParameter($submittedData[$this->parameterTaskName]);
    }

    /**
     * @param $fieldName
     * @param $fieldId
     * @param $fieldValue
     *
     * @return string
     */
    private function buildResourceSelector($fieldName, $fieldId, $fieldValue) : string
    {
        $utilities = $this->configurationUtility->getAllUtilities();

        // define storage for option groups
        $optionGroups = [];

        // loop through all utilities
        foreach ($utilities as $utility) {

            // define option storage
            $options = [];

            // loop through all methods of the current utility
            foreach ($utility['methods'] as $method) {
                $selected = '';

                // add attribute "selected" for existing field value
                if ($fieldValue === $method['method']) {
                    $selected = ' selected="selected"';
                }
                
                if (empty($method['parameters'])) {
                    $label = $method['name'];
                } else  {
                    $label = $method['name'] . ' ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.parameter');
                }

                // add option to option storage
                $options[] = '<option value="' . $method['method'] . '" ' . $selected . '>' . $label . '</option>';
            }

            // add option group to option group storage
            $optionGroups[] = '<optgroup label="' . $utility['class'] . '">' . implode('', $options) . '</optgroup>';
        }

        // return html for select field with option groups and options
        return '<select class="form-control" name="' . $fieldName . '" id="' . $fieldId . '">' . implode('', $optionGroups) . '</select>';
    }
}
