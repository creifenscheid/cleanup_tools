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
}
