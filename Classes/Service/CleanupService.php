<?php
namespace CReifenscheid\CleanupTools\Service;

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
 * Class CleanupService
 *
 * @package CReifenscheid\CleanupTools\Service
 * @author C. Reifenscheid
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
     * Log repository
     *
     * @var \CReifenscheid\CleanupTools\Domain\Repository\LogRepository
     */
    protected $logRepository;

    /**
     * Constructor
     *
     * @param \CReifenscheid\CleanupTools\Service\ConfigurationService $configurationService
     * @param \CReifenscheid\CleanupTools\Domain\Repository\LogRepository $logRepository
     */
    public function __construct(\CReifenscheid\CleanupTools\Service\ConfigurationService $configurationService, \CReifenscheid\CleanupTools\Domain\Repository\LogRepository $logRepository)
    {
        // set configuration service
        $this->configurationService = $configurationService;

        // set log repository
        $this->logRepository = $logRepository;
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

        // if parameter are given
        if ($parameters) {

            if ($this->executionMode === self::USE_METHOD_PROPERTIES) {
            
                // call method with parameter
                $return = call_user_func_array([
                    $service,
                    $method
                ], $parameters);
            } else {
            
                // flag to handle if method can be run - gets false if a property can't be set by setter function
                $runMethod = true;
                
                // set parameter
                foreach ($parameters as $parameter => $value) {
                    $setter = 'set' . ucfirst($parameter);
                    
                    if (method_exists($service, $setter)) {
                        $service->$setter($value);
                    } else {
                        
                        $headline = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:messages.fallback.headline', 'CleanupTools');
                        
                        $message = 'No setter function for property ' . $parameter . ' existing.';
                        
                        // define return flash message
                        $return = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class, $message, $headline, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
                        
                        // disable method processing
                        $runMethod = false;
                        
                        // stop setter check
                        break;
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
        
        if (!$this->dryRun) {
            // get updated log from service
            $log = $service->getLog();

            // set process result state
            if (! $return || ($return instanceof \TYPO3\CMS\Core\Messaging\FlashMessage && $return->getSeverity() === \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR)) {
                $log->setState(false);
                
                // if message is defined during process preparation
                if ($message) {
                    // create new log message
                    $newLogMessage = new \CReifenscheid\CleanupTools\Domain\Model\LogMessage();
                    $newLogMessage->setLog($log);
                    $newLogMessage->setMessage($message);
                }
            }
            
            // set execution context
            $log->setExecutionContext($this->executionContext);
            
            // set processed class
            $log->setService($class);

            // set parameters if given
            if ($parameters) {
                $log->setParameters($parameters);
            }
            
            // add to repository
            $this->logRepository->add($log);
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
        $logRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\CReifenscheid\CleanupTools\Domain\Repository\LogRepository::class);
    
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
