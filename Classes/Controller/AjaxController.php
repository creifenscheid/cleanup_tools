<?php

namespace ChristianReifenscheid\CleanupTools\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ChristianReifenscheid\CleanupTools\Service\CleanupService;
use ChristianReifenscheid\CleanupTools\Service\ConfigurationService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
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
 * @package ChristianReifenscheid\CleanupTools\Controller
 * @author  Christian Reifenscheid
 */
class AjaxController
{
    /**
     * Cleanup service
     *
     * @var \ChristianReifenscheid\CleanupTools\Service\CleanupService
     */
    protected $cleanupService;

    /**
     * Configuration service
     *
     * @var \ChristianReifenscheid\CleanupTools\Service\ConfigurationService
     */
    protected $configurationService;

    /**
     * Localization file
     *
     * @var string
     */
    protected $localizationFile = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cleanupService = GeneralUtility::makeInstance(CleanupService::class);
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->localizationFile = $this->configurationService->getLocalizationFile();
    }

    /**
     * Main action to perform toolbar request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
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
                    'severity' => (string)FlashMessage::ERROR,
                    'headline' => LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'CleanupTools'),
                    'message' => LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message', 'CleanupTools', [$class])
                ];
            }
        } else {
            $return = [
                'severity' => (string)FlashMessage::ERROR,
                'headline' => LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'CleanupTools'),
                'message' => LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message.no-method', 'CleanupTools')
            ];
        }

        // define response body
        $response->getBody()->write(json_encode($return));

        // set and return response
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
