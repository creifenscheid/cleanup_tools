<?php

declare(strict_types=1);

namespace ChristianReifenscheid\CleanupTools\Widgets;

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
 * Class CleanupInformationWidget
 *
 * @package ChristianReifenscheid\CleanupTools\Widgets
 * @author Christian Reifenscheid
 */
class CleanupInformationWidget implements \TYPO3\CMS\Dashboard\Widgets\WidgetInterface
{
    /**
     * @var \TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface
     */
    private $configuration;

    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private $view; 

    /**
     * @var array
     */
    private $options;
    
    /**
     * Configuration service
     * 
     * @var \ChristianReifenscheid\CleanupTools\Service\ConfigurationService
     */
    private $configuationService;

    /**
     * Cleanup service
     * 
     * @var \ChristianReifenscheid\CleanupTools\Service\CleanupService
     */
    private $cleanupService;

    /**
     * Constructor
     * 
     * @param \TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface $configuration
     * @param \TYPO3\CMS\Fluid\View\StandaloneView $view
     * @param array $options
     */
    public function __construct(
        \TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface $configuration,
        \TYPO3\CMS\Fluid\View\StandaloneView $view,
        array $options = []
    ) {
        $this->configuration = $configuration;
        $this->view = $view;
        $this->options = array_merge(
            [
                'template' => 'Widget/CleanupInformation',
            ],
            $options
        );
        
        $this->configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\ChristianReifenscheid\CleanupTools\Service\ConfigurationService::class);
        $this->cleanupService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\ChristianReifenscheid\CleanupTools\Service\CleanupService::class);
    }

    /**
     * Render widget
     * 
     * {@inheritDoc}
     * @see \TYPO3\CMS\Dashboard\Widgets\WidgetInterface::renderWidgetContent()
     */
    public function renderWidgetContent(): string
    {
        // define array to store results from processing services
        $cleanupStates = [];
        
        $this->view->setTemplate($this->options['template']);
        
        $services = $this->configurationService->getServices();
        
        $this->cleanupService->setDryRun(false);
        
        foreach ($services as $service) {
            
            $returnMessage = $this->cleanupService->process($service['class'],$configurationService::FUNCTION_MAIN));
            
            
        }
        
        $this->view->assignMultiple([
            'localizationFile' => $this->configurationService->getLocalizationFile(),
            'cleanupStates' => $cleanupStates
        ]);
        
        return $this->view->render();
    }
}
