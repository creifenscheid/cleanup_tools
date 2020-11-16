<?php
namespace CReifenscheid\CleanupTools\Task;

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
 * Class CleanupAdditionalFieldProvider
 *
 * @package CReifenscheid\CleanupTools\Task
 * @author C. Reifenscheid
 */
class CleanupAdditionalFieldProvider extends \TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider
{
    /**
     * Configuration service
     *
     * @var \CReifenscheid\CleanupTools\Service\ConfigurationService
     */
    protected $configurationService;

    /**
     * Task name
     *
     * @var string
     */
    protected $taskName = 'cleanuptools_cleanuptask_';

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
        $this->configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\CReifenscheid\CleanupTools\Service\ConfigurationService::class);
        $this->localizationFile = $this->configurationService->getLocalizationFile();
    }

    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array $taskInfo
     *            Values of the fields from the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task
     *            The task object being edited. Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
     *            Reference to the scheduler backend module
     *            
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        // field id definitions
        $serviceToProcessId =  $this->taskName . 'serviceToProcess';

        $additionalFields = [];
        
        $currentSchedulerModuleMethod = $schedulerModule->getCurrentAction();
        
        if ($currentSchedulerModuleMethod->equals(\TYPO3\CMS\Scheduler\Task\Enumeration\Action::ADD)) {
            $taskInfo[$serviceToProcessId] = '';
        }
        
        if ($currentSchedulerModuleMethod->equals(\TYPO3\CMS\Scheduler\Task\Enumeration\Action::EDIT)) {
            $taskInfo[$serviceToProcessId] = $task->getServiceToProcess();
        }

        $fieldName = 'tx_scheduler[' . $serviceToProcessId . ']';
        $fieldValue = $taskInfo[$serviceToProcessId];
        $fieldHtml = $this->buildResourceSelector($fieldName, $serviceToProcessId, $fieldValue);
        $additionalFields[$serviceToProcessId] = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.cleanup.fields.serviceToProcess',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $serviceToProcessId
        ];

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values
     *
     * @param array $submittedData
     *            An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
     *            Reference to the scheduler backend module
     *            
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule): bool
    {
        if ($submittedData[$this->taskName . 'serviceToProcess'] && $this->configurationService->getService($submittedData[$this->taskName . 'serviceToProcess'])) {
            return true;
        } else {
            $this->addMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.error.noServices', 'CleanupTools'),\TYPO3\CMS\Core\Messaging\FlashMessage::INFO);
        }

        return false;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array $submittedData
     *            An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task
     *            Reference to the scheduler backend module
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        $task->setServiceToProcess($submittedData[$this->taskName . 'serviceToProcess']);
    }

    /**
     * Build select field with configured services
     *
     * @param   $fieldName
     * @param   $fieldId
     * @param   $fieldValue
     *            
     * @return string
     */
    private function buildResourceSelector($fieldName, $fieldId, $fieldValue): string
    {
        $services = $this->configurationService->getServicesByAdditionalUsage('schedulerTask');
        // loop through all utilities
        if ($services) {
            
            // define option storage
            $options = [];
            
            foreach ($services as $serviceClass) {
                $selected = '';
                
                // add attribute "selected" for existing field value
                if ($fieldValue === $serviceClass['class']) {
                    $selected = ' selected="selected"';
                }
                
                // add option to option storage
                $options[] = '<option value="' . $serviceClass['class'] . '" ' . $selected . '>' . $serviceClass['class'] . '</option>';
            }
            
            // return html for select field with option groups and options
            return '<select class="form-control" name="' . $fieldName . '" id="' . $fieldId . '">' . implode('', $options) . '</select>';
        } else {
            return '
                <div>'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tasks.error.noServices', 'CleanupTools').'</div>
                <input type="hidden" id="' . $fieldId . '" name="' . $fieldName . '" value="" />
            ';
        }
        
    }
}
