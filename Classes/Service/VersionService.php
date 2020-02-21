<?php

namespace SPL\SplCleanupTools\Service;

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
 * Class VersionService
 *
 * @package SPL\SplCleanupTools\Service
 * @author  Christian Reifenscheid
 */
class VersionService  extends \SPL\SplCleanupTools\Service\BaseService
{
    /**
     * Versions
     *
     * @return bool
     */
    public function cleanupVersions () : bool
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(__CLASS__ . ':' . __FUNCTION__ . '::' . __LINE__);
        
        return true;
    }
}
