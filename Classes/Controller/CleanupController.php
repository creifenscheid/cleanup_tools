<?php

namespace SPL\SplCleanupTools\Controller;

use SPL\SplCleanupTools\Service\CleanupService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
 * Class CleanupController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author  Christian Reifenscheid
 */
class CleanupController extends BaseController
{
    /**
     *
     * @var \SPL\SplCleanupTools\Service\CleanupService
     */
    protected $cleanupService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->cleanupService = GeneralUtility::makeInstance(CleanupService::class);
        $this->cleanupService->setExecutionContext(CleanupService::EXECUTION_CONTEXT_BEMODULE);
    }

    /**
     * action index
     *
     * @return void
     */
    public function indexAction() : void
    {
        // assign services to the view
        $this->view->assignMultiple([
            'services' => $this->configurationService->getServices(),
            'errorServices' => $this->configurationService->getErrorServices(),
            'localizationFile' => $this->localizationFile
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
    public function cleanupAction() : void
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

            $result = $this->cleanupService->process($service['class'], $method, $methodParameter);

            if ($result) {

                if (\is_int($result)) {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.dryrun.message', 'SplCleanupTools', [$service['class'],$result]),
                        LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.dryrun.headline', 'SplCleanupTools'),
                        FlashMessage::INFO
                    );

                } else {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.message', 'SplCleanupTools', [$service['class']]),
                        LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.headline', 'SplCleanupTools'),
                        FlashMessage::OK
                    );
                }

            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message', 'SplCleanupTools', [$service['class']]),
                    LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'SplCleanupTools'),
                    FlashMessage::ERROR
                );
            }
        }

        $this->forward('index', 'Cleanup', 'SplCleanupTools');
    }
}
