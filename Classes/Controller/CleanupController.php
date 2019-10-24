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
 * Class CleanupController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author Christian Reifenscheid
 */
class CleanupController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * Module configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * action index
     *
     * @return void
     */
    public function indexAction(): void
    {
        // init configurationManager
        $configurationManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        
        // init typoscript service
        $typoscriptService = $this->objectManager->get(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class);
        
        // get module configuration
        $this->configuration = $typoscriptService->convertTypoScriptArrayToPlainArray($extbaseFrameworkConfiguration['module.']['tx_splcleanuptools.']);
        
        // define a storage for utilities
        $utilities = [];

        // loop through configured utilities
        foreach ($this->configuration['utilities'] as $utilityClass => $utilityConfiguration) {
            
            // set utility information
            $utilities[$utilityClass] = [
                'name' => end(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('\\', $utilityClass)),
                'class' => $utilityClass
            ];
            
            if ($utilityConfiguration['color']) {
                $utilities[$utilityClass]['color'] = $utilityConfiguration['color'];
            }
            
            // get and store class methods
            $methods = get_class_methods(new $utilityClass());
            
            // loop through every method
            foreach ($methods as $method) {
                
                // check method
                if ($this->checkMethodBlacklist($method, $utilityConfiguration['methods'])) {
                    
                    $reflection = new \ReflectionMethod($utilityClass, $method);
                    $parameters = $reflection->getParameters();
                    
                    $methodParameters = [];
                    
                    foreach ($parameters as $parameter) {
                        
                        $methodParameters[] = [
                            'name' => $parameter->getName(),
                            'label' => $this->unLowerCamelCase($parameter->getName()),
                            'formType' => $this->configuration['mapping']['parameter'][$parameter->getName()]
                        ];
                    }
                    
                    // prepare method information for view
                    $utilities[$utilityClass]['methods'][] = [
                        'name' => $this->unLowerCamelCase($method),
                        'method' => $method,
                        'parameters' => $methodParameters
                    ];
                }
            }
        }

        // assign utilities to the view
        $this->view->assign('utilities', $utilities);
    }

    /**
     * action cleanup
     *
     * @return void
     * @throws \ReflectionException
     */
    public function cleanupAction(): void
    {
        // get arguments from request
        $arguments = $this->request->getArguments();

        // check for required arguments
        if ($arguments['utilityAction'] && $arguments['utilityClass']) {

            // get utility and utility action from arguments
            $utilityClass = $arguments['utilityClass'];
            $utilityActionName = $arguments['utilityAction'];
            $utilityActionParameter = $arguments['parameters'];

            // init utility
            $utility = $this->objectManager->get($utilityClass);

            // call action in utility
            if ($utilityActionParameter) {
                $result = call_user_func_array([$utility,$utilityActionName], $utilityActionParameter);
            } else {
                $result = $utility->$utilityActionName();
            }

            $this->view->assignMultiple([
                'result' => $result,
                'service' => $utilityClass
            ]);
        }
    }
    
    /**
     * Toolbar action
     *
     *
     */
    public function toolbarAction ()
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        $queryParams = $request->getQueryParams();
        
        $clearCmd = $queryParams['clearCmd'] ? : null;
        
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump([$request,$clearCmd, $queryParams], __CLASS__ . ':'. __FUNCTION__ .'::'.__LINE__);
        
        /**
         * ToDo:
         * Mapping von clearCmd auf entsprechende Utility/-Klasse - vgl. cleanUpAction
         * Ggf. einen Konstruktor aufsetzen, welcher die TS-Konfiguration aufbereitet und ein Array mit den Utilities und deren Methoden zur Verfügung stellt.
         * Sollte dann auch von der IndexAction verwendet werden können.
         * 
         * $result = $utility->$utilityActionName();
         */
        
        
        
        return new \TYPO3\CMS\Core\Http\HtmlResponse('');
    }
    
    /**
     * Function to check if a method of a utility is blacklisted
     * 
     * @param string $method
     * @param array $configuration
     * @return bool
     */
    private function checkMethodBlacklist ($method, $configuration) : bool {
        
        // get configured includes and excludes
        $excludes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['excludes']);
        
        // if method is in excludes - return false to skip the method
        if (in_array($method, $excludes)) {
            return false;
        }
        
        // if method is not in the configuration - return true to add the method
        return true;
    }
    
    /**
     * Function to transform strings from lowerCamelCase to string with spaces
     * 
     * @param string $input
     * @return string
     */
    private function unLowerCamelCase (string $input) : string {
        
        // 1. turn lowerCamelCase method name into lower case underscored
        // 2. replace underscores by space
        // 3. set the first char to upper case
        return ucfirst(str_replace('_', ' ', \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($input)));
    }
}
