<?php
namespace ChristianReifenscheid\CleanupTools\Service;

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
 * Class CleanupService
 *
 * @package ChristianReifenscheid\CleanupTools\Service
 * @author Christian Reifenscheid
 */
class CleanupService
{

    /**
     * Execution contexts
     */
    const EXECUTION_CONTEXT_BEMODULE = 0;

    const EXECUTION_CONTEXT_TOOLBAR = 1;

    const EXECUTION_CONTEXT_SCHEDULER = 2;

    const EXECUTION_CONTEXT_PREVIEWRENDERER = 3;
    
    const EXECUTION_CONTEXT_DBHOOK = 4;
    
    const EXECUTION_CONTEXT_DASHBOARD = 5;

    // Execution mode
    const USE_CLASS_PROPERTIES = 0;

    const USE_METHOD_PROPERTIES = 1;

    /**
     * Execution context
     *
     * @var int
     */
    protected $executionContext = self::EXECUTION_CONTEXT_BEMODULE;

    /**
     * Execution mode
     *
     * @var int
     */
    protected $executionMode = self::USE_CLASS_PROPERTIES;

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
     * @var \ChristianReifenscheid\CleanupTools\Domain\Repository\LogRepository
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
        $this->configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConfigurationService::class);

        // init log repository
        $this->logRepository = $this->objectManager->get(\ChristianReifenscheid\CleanupTools\Domain\Repository\LogRepository::class);
    }

    /**
     * Set execution context
     *
     * @param int $executionContext
     *
     * @return void
     */
    public function setExecutionContext(int $executionContext): void
    {
        $this->executionContext = $executionContext;
    }

    /**
     * Set execution mode
     *
     * @param int $executionMode
     *
     * @return void
     */
    public function setExecutionMode(int $executionMode): void
    {
        $this->executionMode = $executionMode;
    }

    /**
     * Set dry run
     *
     * @param boolean $dryRun
     *
     * @return void
     */
    public function setDryRun($dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Function to initialize a utility and call the requested method
     *
     * @param string $class
     * @param string $method
     * @param array $parameters
     *
     * @return int|bool
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function process(string $class, string $method, array $parameters = null)
    {
        // init service
        $service = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($class);

        // write log if it's not a dry run
        if (!$this->dryRun) {
            $log = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\ChristianReifenscheid\CleanupTools\Domain\Model\Log::class);
            $log->setCrdate(time());

            if ($GLOBALS['BE_USER']->user['uid']) {
                $log->setCruserId($GLOBALS['BE_USER']->user['uid']);
            }

            $log->setExecutionContext($this->executionContext);
            $log->setService($class);

            if ($parameters) {
                $log->setParameters($parameters);
            }

            // set log in service
            $service->setLog($log);
        }

        // if parameter are given
        if ($parameters) {

            if ($this->executionMode === self::USE_METHOD_PROPERTIES) {
                // call method with parameter
                $return = call_user_func_array([
                    $service,
                    $method
                ], $parameters);
            } else {
                // flag to handle if method can be run
                $runMethod = true;
                
                // set parameter
                foreach ($parameters as $parameter => $value) {
                    $setter = 'set' . ucfirst($parameter);
                    
                    if (method_exists($service, $setter)) {
                        $service->$setter($value);
                    } else {
                        
                        $message = 'No setter function for property ' . $parameter . ' existing.';
                        
                        // create new message
                        $newLogMessage = new \ChristianReifenscheid\CleanupTools\Domain\Model\LogMessage();
                        $newLogMessage->setLog($log);
                        $newLogMessage->setMessage($message);
                        
                        // add message to log
                        $log->addMessage($newLogMessage);
                        
                        $return = false;
                        $runMethod = false;
                    }
                }
                
                // call method
                if ($runMethod) {
                    $return = $service->$method();
                }
            }
        } else {

            // set dry run
            $service->setDryRun($this->dryRun);

            // call method
            $return = $service->$method();
        }

        if (! $return || ($return instanceof \TYPO3\CMS\Core\Messaging\FlashMessage && $return->getSeverity() === \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR)) {
            $log->setState(false);
        }

        // get updated log from service and add to repository
        if (!$this->dryRun) {
            $this->logRepository->add($service->getLog());
        }

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
        $persistenceManager->persistAll();

        return $return;
    }
    
    /**
     * Deleted log entries
    *
    * @param string $logLifetime
    *
    * @return bool
    */
    public function processHistoryCleanup(string $logLifetime) : bool
    {
        
        // init log repository
        $logRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\ChristianReifenscheid\CleanupTools\Domain\Repository\LogRepository::class);
    
        // create timestamp of log lifetime
        $logLifetime = strtotime('-' . $logLifetime);

        // get all logs older then
        $logsToDelete = $logRepository->findOlderThen($logLifetime);

        // mark log as deleted
        if ($logsToDelete) {
            foreach ($logsToDelete as $logToDelete) {
                $logRepository->remove($logToDelete);
            }
        }
        
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
        $persistenceManager->persistAll();
        
        return true;
    }
}
