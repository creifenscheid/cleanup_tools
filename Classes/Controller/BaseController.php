<?php
namespace creifenscheid\CleanupTools\Controller;

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
 * Class BaseController
 *
 * @package creifenscheid\CleanupTools\Controller
 * @author C. Reifenscheid
 */
class BaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     *
     * @var \creifenscheid\CleanupTools\Service\ConfigurationService
     */
    protected $configurationService;

    /**
     * Localization file
     *
     * @var string
     */
    protected $localizationFile = '';

    /**
     * Constructor
     *
     * @param \creifenscheid\CleanupTools\Service\ConfigurationService $configurationService
     */
    public function __construct(\creifenscheid\CleanupTools\Service\ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
        $this->localizationFile = $this->configurationService->getLocalizationFile();
    }
}
