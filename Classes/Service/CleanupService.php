<?php
namespace ChristianReifenscheid\CleanupTools\Service;

use ChristianReifenscheid\CleanupTools\Domain\Model\Log;
use ChristianReifenscheid\CleanupTools\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \ReflectionClass;

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

    const EXECUTION_CONTEXT_DRAWITEMHOOK = 3;

    const EXECUTION_CONTEXT_DBHOOK = 4;

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
     * @var LogRepository
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
        // todo: check
        if ($parameters && $this->executionMode !== self::USE_METHOD_PROPERTIES) {
            $service = $this->objectManager->get($class, $parameters);
        } else {
            $service = $this->objectManager->get($class);
        }

        // set up reflection
        //todo: del if objectmngr wrks $reflection = new \ReflectionClass($service);
        $reflection = $this->objectManager->get(ReflectionClass::class, [$service]);

        // write log
        //todo: del if objectmngr wrks $log = new Log();
        $log = $this->objectManager->get(Log::class);
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
                    $propertyReflection = $reflection->getProperty($parameter);

                    if ($propertyReflection->isPublic()) {
                        $service->$parameter = $value;
                    } else {
                        $setter = 'set' . ucfirst($parameter);

                        if (method_exists($service, $setter)) {
                            $service->$setter($value);
                        } else {

                            $message = 'Property ' . $parameter . ' is not public and no setter is given.';

                            // create new message
                            $newLogMessage = new \SPL\SplCleanupTools\Domain\Model\LogMessage();
                            $newLogMessage->setLog($log);
                            $newLogMessage->setMessage($message);

                            // add message to log
                            $log->addMessage($newLogMessage);

                            $return = false;
                            $runMethod = false;
                        }
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

        if (! $return || ($return instanceof FlashMessage && $return->getSeverity() === FlashMessage::ERROR)) {
            $log->setState(false);
        }

        // get updated log from service and add to repository
        $this->logRepository->add($service->getLog());

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        return $return;
    }
}
