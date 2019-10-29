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
 * @author Christian Reifenscheid
 */
class CleanupAdditionalFieldProvider extends \TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider
{
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

        // Initialize selected fields
        // resource
        if (!isset($taskInfo['scheduler_jobs_resource'])) {
            $taskInfo['scheduler_jobs_resource'] = $this->resource;
            if ($currentSchedulerModuleAction->equals(\TYPO3\CMS\Scheduler\Task\Enumeration\Action::EDIT)) {
                $taskInfo['scheduler_jobs_resource'] = $task->resource;
            }
        }
        $fieldName = 'tx_scheduler[scheduler_jobs_resource]';
        $fieldId = 'scheduler_jobs_resource';
        $fieldValue = $taskInfo['scheduler_jobs_resource'];
        $fieldHtml = '';#$this->buildResourceSelector($fieldName, $fieldId, $fieldValue);
        $additionalFields[$fieldId] = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:fnn_amt24manager/Resources/Private/Language/locallang.xlf:label.jobsTask.resource',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId
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
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        $validData = false;

        // resource
        if (!isset($submittedData['scheduler_jobs_resource'])) {
            $validData = true;
        } elseif ($submittedData['scheduler_jobs_resource']) {
            $validData = true;
        }

        return $validData;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array                                  $submittedData An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task          Reference to the scheduler backend module
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        $task->resource = $submittedData['scheduler_jobs_resource'];
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
        /*$service = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Fnn\FnnAmt24\Service\Service::class);
        $resources = $service->getResources();

        $options = [];

        foreach ($resources as $key => $value) {
            $selected = '';
            if ($fieldValue === $value) {
                $selected = ' selected="selected"';
            }
            $options[] = '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
        }

        return '<select class="form-control" name="' . $fieldName . '" id="' . $fieldId . '">' . implode('', $options) . '</select>';*/
    }
}
