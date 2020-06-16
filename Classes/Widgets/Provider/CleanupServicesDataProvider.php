<?php
declare(strict_types=1);
namespace ChristianReifenscheid\CleanupTools\Widgets\Provider;

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
 * Class CleanupServicesDataProvider
 *
 * @package ChristianReifenscheid\CleanupTools\Widgets\Provider
 * @author Christian Reifenscheid
 */
class CleanupServicesDataProvider implements \TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface
{
    /**
     * @var \ChristianReifenscheid\CleanupTools\Service\ConfigurationService
     */
    private $configurationService;
    
    /**
     * @var \ChristianReifenscheid\CleanupTools\Service\CleanupService
     */
    private $cleanupService;
    
    public function __construct(\ChristianReifenscheid\CleanupTools\Service\ConfigurationService $configurationService, \ChristianReifenscheid\CleanupTools\Service\CleanupService $cleanupService)
    {
        $this->configurationService = $configurationService;
        $this->cleanupService = $cleanupService;
    }
    
    /**
     * Returns items
     * 
     * @return array
     */
    public function getItems(): array
    {
        // define array to store results from processing services
        $cleanupInformation = [];
        
        // get all enabled services
        $services = $this->configurationService->getServices();
        
        // configure cleanup service
        $this->cleanupService->setDryRun(true);
        $this->cleanupService->setExecutionContext($this->cleanupService::EXECUTION_CONTEXT_DASHBOARD);
        
        // loop through services
        foreach ($services as $service) {
            // process service in dry-run mode
            $returnMessage = $this->cleanupService->process($service['class'], $this->configurationService::FUNCTION_MAIN);
            
            // store information
            $information = [
                'name' => $service['name'],
                'information' => $returnMessage->getMessage()
            ];
            
            $cleanupInformation[] = $information;
        }
        
        return $cleanupInformation;
    }
}