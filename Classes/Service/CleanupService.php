<?php

namespace SPL\SplCleanupTools\Service;

use SPL\SplCleanupTools\Domain\Model\Log;
use SPL\SplCleanupTools\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use function call_user_func_array;

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
     * Execution contexts
     */
    const EXECUTION_CONTEXT_BEMODULE = 0;
    const EXECUTION_CONTEXT_TOOLBAR = 1;
    const EXECUTION_CONTEXT_SCHEDULER = 2;
    const EXECUTION_CONTEXT_DRAWITEMHOOK = 3;
    const EXECUTION_CONTEXT_DBHOOK = 4;

    /**
     * Execution context
     *
     * @var int
     */
    protected $executionContext = 0;
    
    /**
     * Dry run
     * 
     * @var bool
     */
    protected $dryRun = true;

    /**
     * Configuration service
     *
     * @var ConfigurationService
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
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // init configuration service
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        // init log repository
        $this->logRepository = $this->objectManager->get(LogRepository::class);
    }

    /**
     * Set execution context
     *
     * @param int $executionContext
     *
     * @return void
     */
    public function setExecutionContext(int $executionContext) : void
    {
        $this->executionContext = $executionContext;
    }

    /**
     * Set dry run
     * 
     * @param boolean $dryRun
     * 
     * @return void
     */
    public function setDryRun($dryRun) : void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Function to initialize a utility and call the requested method
     *
     * @param string $class
     * @param string $method
     * @param array  $parameters
     *
     * @return int|bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function process(string $class, string $method, array $parameters = null)
    {
        // define return var
        $return = false;
        
        // init service
        $service = $this->objectManager->get($class);
        
        // if parameter are given
        if ($parameters) {

            // set parameter
            foreach($parameters as $parameter => $value) {
                $setter = 'set'.ucfirst($parameter);
                $service->$setter($value);
            }

            // call method
            $return = $service->$method();
        } else {
            
            // set dry run
            $service->setDryRun($this->dryRun);
            
            // call method
            $return = $service->$method();
        }
        
        // write log
        $log = new Log();
        $log->setCrdate(time());
        
        if ($GLOBALS['BE_USER']->user['uid']) {
            $log->setCruserId($GLOBALS['BE_USER']->user['uid']);
        }
        
        $log->setExecutionContext($this->executionContext);
        $log->setService($class);
        
        $this->logRepository->add($log);
        
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        return $return;
    }
}
