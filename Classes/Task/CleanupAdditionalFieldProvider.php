<?php

namespace SPL\SplCleanupTools\Task;

use SPL\SplCleanupTools\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

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
class CleanupAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Cleanup method
     *
     * @var string
     */
    protected $cleanupMethod = '';

    /**
     * Configuration service
     *
     * @var \SPL\SplCleanupTools\Service\ConfigurationService $configurationService
     */
    protected $configurationService;

    /**
     * Task name
     *
     * @var string
     */
    protected $cleanupMethodTaskName = 'scheduler_cleanuptools_cleanupmethod';

    /**
     * Localization file
     *
     * @var string
     */
    protected $localizationFile = '';

    /**
     * CleanupAdditionalFieldProvider constructor.
     */
    public function __construct()
    {
        // init configurationService
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->localizationFile = $this->configurationService->getLocalizationFile();
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
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $currentSchedulerModuleMethod = $schedulerModule->getCurrentAction();

        $additionalFields = [];

        // Initialize selected fields
        // Cleanup method
        if (!isset($taskInfo[$this->cleanupMethodTaskName])) {
            $taskInfo[$this->cleanupMethodTaskName] = $this->cleanupMethod;
            if ($currentSchedulerModuleMethod->equals(Action::EDIT)) {
                $taskInfo[$this->cleanupMethodTaskName] = $task->getCleanupMethod();
            }
        }

        $fieldName = 'tx_scheduler[' . $this->cleanupMethodTaskName . ']';
        $fieldValue = $taskInfo[$this->cleanupMethodTaskName];
        $fieldHtml = $this->buildResourceSelector($fieldName, $this->cleanupMethodTaskName, $fieldValue);
        $additionalFields[$this->cleanupMethodTaskName] = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.fields.cleanupmethod',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $this->cleanupMethodTaskName
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
        if ($this->configurationService->getServiceByMethod($submittedData[$this->cleanupMethodTaskName])) {
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
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->setCleanupMethod($submittedData[$this->cleanupMethodTaskName]);
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
        $services = $this->configurationService->getServicesByAdditionalUsage('schedulerTask');

        // define storage for option groups
        $optionGroups = [];

        // loop through all utilities
        foreach ($services as $serviceClass => $serviceMethods) {

            // define option storage
            $options = [];

            // loop through all methods of the current utility
            foreach ($serviceMethods as $method) {
                $selected = '';

                // add attribute "selected" for existing field value
                if ($fieldValue === $method['method']) {
                    $selected = ' selected="selected"';
                }

                if (empty($method['parameters'])) {
                    $label = $method['method'];
                } else {
                    $label = $method['method'] . ' ' . LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.parameter');
                }

                // add option to option storage
                $options[] = '<option value="' . $method['method'] . '" ' . $selected . '>' . $label . '</option>';
            }

            // add option group to option group storage
            $optionGroups[] = '<optgroup label="' . $serviceClass . '">' . implode('', $options) . '</optgroup>';
        }

        // return html for select field with option groups and options
        return '<select class="form-control" name="' . $fieldName . '" id="' . $fieldId . '">' . implode('', $optionGroups) . '</select>';
    }
}
