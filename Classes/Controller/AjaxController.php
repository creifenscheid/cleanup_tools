<?php

namespace SPL\SplCleanupTools\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SPL\SplCleanupTools\Service\CleanupService;
use SPL\SplCleanupTools\Service\ConfigurationService;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
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
 * Class AjaxController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author  Christian Reifenscheid
 */
class AjaxController extends BaseScriptClass
{
    /**
     * Cleanup service
     *
     * @var \SPL\SplCleanupTools\Service\CleanupService
     */
    protected $cleanupService;

    /**
     * Configuration service
     *
     * @var \SPL\SplCleanupTools\Service\ConfigurationService
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
        parent::__construct();
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

        // get execution context from query params
        $executionContext = $queryParams['executionContext'] ? : null;
        $this->cleanupService->setExecutionContext($executionContext);
        
        // get clean class from query params
        $class = $queryParams['class'] ? : null;
        
        // get clean command from query params
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
                
                if (\is_int($result)) {
                    $return = [
                        'status' => 'info',
                        'headline' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.dryrun.headline', 'SplCleanupTools'),
                        'message' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.dryrun.message', 'SplCleanupTools', [$class,$result])
                    ];
                    
                } else {
                    $return = [
                        'status' => 'ok',
                        'headline' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.headline', 'SplCleanupTools'),
                        'message' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.message', 'SplCleanupTools', [$class])
                    ];
                }
                
                
            } else {
                $return = [
                    'status' => 'error',
                    'headline' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'SplCleanupTools'),
                    'message' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message', 'SplCleanupTools', [$class])
                ];
            }
        } else {
            $return = [
                'status' => 'error',
                'headline' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline', 'SplCleanupTools'),
                'message' => LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message.no-method', 'SplCleanupTools')
            ];
        }

        // define response body
        $response->getBody()->write(json_encode($return));

        // set and return response
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
