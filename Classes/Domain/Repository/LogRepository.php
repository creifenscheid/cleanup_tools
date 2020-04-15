<?php

namespace SPL\SplCleanupTools\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

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
 * Class LogRepository
 *
 * @package SPL\SplCleanupTools\Domain\Repository
 * @author  Christian Reifenscheid
 */
class LogRepository extends BaseRespository
{

    /**
     * Function to get logs by service and method
     *
     * @param string $service
     *
     * @return null|object
     */
    public function findByService(string $service) : ?object
    {
        $query = $this->createQuery();

        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        $query->matching(
            $query->logicalAnd(
                [
                    $query->equals('service', $service)
                ]
            )
        );

        return $query->execute()->getFirst();
    }
}

