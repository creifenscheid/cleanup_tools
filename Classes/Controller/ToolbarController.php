<?php
namespace SPL\SplCleanupTools\Controller;

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
 * Class ToolbarController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author Christian Reifenscheid
 */
class ToolbarController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    /**
     * Cleanup utility
     * 
     * @var \SPL\SplCleanupTools\Utility\CleanupUtility
     */
    protected $cleanupUtility;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->cleanupUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Utility\CleanupUtility::class);
    }

    /**
     * Main action to perform toolbar request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function mainAction(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) : \Psr\Http\Message\ResponseInterface
    {
        // get query params
        $queryParams = $request->getQueryParams();

        // get clean command from query params
        $action = $queryParams['action'] ? : null;

        // if cleanCmd is given
        if ($action) {

            // process action through cleanup utility
            $processResult = $this->cleanupUtility->processAction($action);

            if ($processResult) {
                $return = [
                    'status' => 'ok',
                    'headline' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.headline','SplCleanupTools'),
                    'message' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.success.message','SplCleanupTools',[$action])
                ];
            } else {
                $return = [
                    'status' => 'error',
                    'headline' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline','SplCleanupTools'),
                    'message' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message','SplCleanupTools',[$action])
                ];
            }
        } else {
            $return = [
                'status' => 'error',
                'headline' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.headline','SplCleanupTools'),
                'message' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.error.message.no-action','SplCleanupTools')
            ];
        }

        // define response body
        $response->getBody()->write(json_encode($return));

        // set and return response
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
}
