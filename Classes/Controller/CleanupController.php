<?php
namespace CReifenscheid\CleanupTools\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class CleanupController
 *
 * @package CReifenscheid\CleanupTools\Controller
 * @author C. Reifenscheid
 */
class CleanupController extends BaseController
{

    /**
     *
     * @var \CReifenscheid\CleanupTools\Service\CleanupService
     */
    protected $cleanupService;

    /**
     * Constructor
     *
     * @param \CReifenscheid\CleanupTools\Service\CleanupService $cleanupService
     */
    public function __construct(\CReifenscheid\CleanupTools\Service\CleanupService $cleanupService)
    {
        $this->cleanupService = $cleanupService;
        $this->cleanupService->setExecutionContext(\CReifenscheid\CleanupTools\Service\CleanupService::EXECUTION_CONTEXT_BEMODULE);
    }

    /**
     * action index
     *
     * @return void
     */
    public function indexAction(): void
    {
        if (! empty($this->configurationService->getErrorServices())) {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error-services.message', 'CleanupTools', [
                implode(',', $this->configurationService->getErrorServices())
            ]), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error-services.headline', 'CleanupTools'), \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING);
        }

        // assign services to the view
        $this->view->assignMultiple([
            'services' => $this->configurationService->getServices()
        ]);
    }

    /**
     * action cleanup
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function cleanupAction(): void
    {
        // get arguments from request
        $arguments = $this->request->getArguments();

        // check for required arguments
        if ($arguments['service']) {

            // get service from arguments
            $service = $this->configurationService->getService($arguments['service']);
            $method = $arguments['method'];
            $methodParameter = $arguments['parameters'];

            // check if parameter value is set
            foreach ($methodParameter as $parameterName => $parameterValue) {

                // if parameter is empty
                if ($parameterValue === '') {

                    // set default value exists
                    if (\array_key_exists('default', $service['method']['parameters'][$parameterName])) {
                        $methodParameter[$parameterName] = $service['method']['parameters'][$parameterName]['default'];
                    } else {
                        $methodParameter[$parameterName] = null;
                    }
                } else {
                    settype($methodParameter[$parameterName], $service['method']['parameters'][$parameterName]['type']);
                }
            }

            $return = $this->cleanupService->process($service['class'], $method, $methodParameter);

            if ($return) {
                $this->addFlashMessage($return->getMessage(), $return->getTitle(), $return->getSeverity());
            } else {
                $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message', 'CleanupTools', [
                    $service['class']
                ]), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'CleanupTools'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
            }

            $this->forward('index', 'Cleanup', 'CleanupTools');
        }
    }
}
