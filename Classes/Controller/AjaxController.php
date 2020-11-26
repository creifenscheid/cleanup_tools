<?php

namespace CReifenscheid\CleanupTools\Controller;

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
 * Class AjaxController
 *
 * @package CReifenscheid\CleanupTools\Controller
 * @author  C. Reifenscheid
 */
class AjaxController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Cleanup service
     *
     * @var \CReifenscheid\CleanupTools\Service\CleanupService
     */
    protected $cleanupService;

    /**
     * Configuration service
     *
     * @var \CReifenscheid\CleanupTools\Service\ConfigurationService
     */
    protected $configurationService;

    /**
     * Constructor
     *
     * @param \CReifenscheid\CleanupTools\Service\CleanupService $cleanupService
     * @param \CReifenscheid\CleanupTools\Service\ConfigurationService $configurationService
     */
    public function __construct(\CReifenscheid\CleanupTools\Service\CleanupService $cleanupService, \CReifenscheid\CleanupTools\Service\ConfigurationService $configurationService)
    {
        $this->cleanupService = $cleanupService;
        $this->configurationService = $configurationService;
    }

    /**
     * Main action to perform toolbar request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function mainAction(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // get query params
        $queryParams = $request->getQueryParams();

        // get execution context
        if ($queryParams['executionContext']) {
            $this->cleanupService->setExecutionContext($queryParams['executionContext']);
        }
        
        // get execution mode
        if ($queryParams['executionMode']) {
            $this->cleanupService->setExecutionMode($queryParams['executionMode']);
        }
        
        // get clean class
        $class = $queryParams['class'] ? : null;
        
        // get clean command
        $method = $queryParams['method'] ? : null;

        // get record uid if a specific record shall be cleaned
        $recordUid = $queryParams['recordUid'] ? : null;

        // if cleanCmd is given
        if ($class && $method) {
            
            // process method through cleanup service
            if ($recordUid) {
                $result = $this->cleanupService->process($class, $method, ['recordUid' => (int)$recordUid]);
            } else {
                $result = $this->cleanupService->process($class, $method);
            }
            
            if ($result) {
                $return = [
                    'severity' => (string)$result->getSeverity(),
                    'headline' => $result->getTitle(),
                    'message' => $result->getMessage()
                ];
                
            } else {
                $return = [
                    'severity' => (string)\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    'headline' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'CleanupTools'),
                    'message' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message', 'CleanupTools', [$class])
                ];
            }
        } else {
            $return = [
                'severity' => (string)\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                'headline' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'CleanupTools'),
                'message' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message.no-method', 'CleanupTools')
            ];
        }
        
        return new \TYPO3\CMS\Core\Http\JsonResponse($return); 
    }
}
