<?php

namespace SPL\SplCleanupTools\Service;

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
 * Class CleanupService
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class CleanupService
{
    /**
     * Processing contexts
     */
    const PROCESSING_CONTEXT_BEMODULE = 0;
    const PROCESSING_CONTEXT_TOOLBAR = 1;
    const PROCESSING_CONTEXT_SCHEDULER = 2;
    const PROCESSING_CONTEXT_DRAWITEMHOOK = 3;
    const PROCESSING_CONTEXT_DBHOOK = 4;
    
    /**
     * Processing context
     * 
     * @var int
     */
    protected $processingContext = 0;
    
    /**
     * Configuration service
     *
     * @var \SPL\SplCleanupTools\Service\ConfigurationService
     */
    protected $configurationService;

    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;
    
    /**
     * Log repository
     * 
     * @var \SPL\SplCleanupTools\Domain\Repository\LogRepository
     */
    protected $logRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        // init object manager
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        // init configuration service
        $this->configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SPL\SplCleanupTools\Service\ConfigurationService::class);
        
        // init log repository
        $this->logRepository = $this->objectManager->get(\SPL\SplCleanupTools\Domain\Repository\LogRepository::class);
    }
    
    /**
     * Set processing context
     * 
     * @param int $processingContext
     * 
     * @return void
     */
    public function setProcessingContext (int $processingContext) : void {
        $this->processingContext = $processingContext;
    }

    /**
     * Function to initialze a utility and call the requested action
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function processAction(string $method, array $parameters = null) : bool
    {
        // define return var
        $return = false;

        // get service of cleanCmd
        $serviceConfiguration = $this->configurationService->getServiceByMethod($method);

        // if a service is returned
        if ($serviceConfiguration) {

            // get service class
            $serviceClass = $serviceConfiguration['class'];

            // init service
            $service = $this->objectManager->get($serviceClass);

            // if parameter are given
            if ($parameters) {
                // call action with parameter
                $return = \call_user_func_array([$service, $method], $parameters);
            } else {

                // call action
                $return = $service->$method();
            }
            
            // write log
            $log = new \SPL\SplCleanupTools\Domain\Model\Log();
            $log->setCrdate(time());
            
            if ($GLOBALS['BE_USER']->user['uid']) {
                $log->setCruserId($GLOBALS['BE_USER']->user['uid']);
            }
            
            $log->setProcessingContext($this->processingContext);
            $log->setService($serviceClass);
            $log->setAction($method);
            
            $this->logRepository->add($log);
            
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
            $persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
            $persistenceManager->persistAll();
        }

        return $return;
    }
}
