<?php

namespace SPL\SplCleanupTools\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class CleanupController
 *
 * @package SPL\SplCleanupTools\Controller
 * @author  Christian Reifenscheid
 */
class CleanupController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action index
     *
     * @return void
     */
    public function indexAction() : void
    {   
        // define a storage for all found utility paths
        $utilityPaths = [];
        
        // define a storage for utilities for the view
        $utilities = [];
        
        // define path to utility folder
        $utilityFolder = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('spl_cleanup_tools').'Classes/Utility/';
        
        // read the folder
        if ($handle = opendir($utilityFolder)) {
            
            // walk through the files
             while (false !== ($file = readdir($handle))) {
                 if ($file !== '.' && $file !== '..') {
                     
                     // push the utilities into the storage
                     $utilityPaths[] = $utilityFolder.$file;
                 }
            }
            
            closedir($handle);
        }
        
        foreach ($utilityPaths as $utilityPath) {
            $utilityClass = $this->getClassFromFile($utilityPath);
            // ToDo store utilities and there functions in a multidimensional array
            var_dump($utilityClass);
        }
       
        die();
    }

    /**
     * action cleanup
     *
     * @return void
     * @throws \ReflectionException
     */
    public function cleanupAction() : void
    {
        // get arguments from request
        $arguments = $this->request->getArguments();

        // check for required arguments
        if ($arguments['utilityAction'] && $arguments['utility']) {

            // get utility and utility action from arguments
            $utilityName = $arguments['utility'];
            $utilityActionName = $arguments['utilityAction'];

            // define reflection object
            /** @var \ReflectionClass $className */
            $thisReflection = new \ReflectionClass($this);

            // get namespace
            $thisNamespace = $thisReflection->getNamespaceName();

            // generate utility namespace
            $utilityNamespace = str_replace('Controller', 'Utility', $thisNamespace) . '\\' . $utilityName . 'Utility';

            // init utility
            $utility = $this->objectManager->get($utilityNamespace);

            // call action in utility
            $result = $utility->$utilityActionName();

            $this->view->assignMultiple([
                'result' => $result
            ]);
        }
    }
    
    private function getClassFromFile($filePath)
    {
        // grab the file content
        $contents = file_get_contents($filePath);
        
        // define vars for namespace and class name
        $namespace = $class = '';
        
        // set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $gettingNamespace = $gettingClass = false;
        
        // go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {
            
            // if this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $gettingNamespace = true;
            }
            
            // if this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $gettingClass = true;
            }
            
            // while we're grabbing the namespace name...
            if ($gettingNamespace === true) {
                
                // if the token is a string or the namespace separator...
                if(is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    
                    // append the token's value to the name of the namespace
                    $namespace .= $token[1];
                    
                }
                
                else if ($token === ';') {
                    
                    // if the token is the semicolon, then we're done with the namespace declaration
                    $gettingNamespace = false;
                    
                }
            }
            
            // while we're grabbing the class name...
            if ($gettingClass === true) {
                
                // if the token is a string, it's the name of the class
                if(is_array($token) && $token[0] == T_STRING) {
                    
                    // store the token's value as the class name
                    $class = $token[1];
                    
                    break;
                }
            }
        }
        
        // build and return the fully-qualified class name
        return $namespace ? $namespace . '\\' . $class : $class;  
    }
}
