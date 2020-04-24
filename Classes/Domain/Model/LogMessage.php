<?php
namespace ChristianReifenscheid\CleanupTools\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use ChristianReifenscheid\CleanupTools\Domain\Model\Log;

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
 * Class LogMessage
 *
 * @package ChristianReifenscheid\SplCleanupTools\Domain\Model
 * @author Christian Reifenscheid
 */
class LogMessage extends AbstractEntity
{

    /**
     * Log
     *
     * @var Log
     */
    protected $log;

    /**
     * Message
     *
     * @var string
     */
    protected $message = '';

    /**
     * Local lang key
     *
     * @var string
     */
    protected $localLangKey = '';

    /**
     * Local lang arguments
     *
     * @var string
     */
    protected $localLangArguments = '';

    /**
     * Returns log
     *
     * @return Log
     */
    public function getLog(): Log
    {
        return $this->log;
    }

    /**
     * Set log
     *
     * @param Log $log
     * @return void
     */
    public function setLog(Log $log): void
    {
        $this->log = $log;
    }

    /**
     * Message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Message
     *
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Return local lang key
     *
     * @return string
     */
    public function getLocalLangKey(): string
    {
        return $this->localLangKey;
    }

    /**
     * Set local lang key
     *
     * @param string $localLangKey
     * @return void
     */
    public function setLocalLangKey(string $localLangKey): void
    {
        $this->localLangKey = $localLangKey;
    }

    /**
     * Return local lang arguments
     *
     * @return array
     */
    public function getLocalLangArguments(): array
    {
        if (unserialize($this->localLangArguments)) {
            return unserialize($this->localLangArguments);
        }

        return [];
    }

    /**
     * Set local lang arguments
     *
     * @param array $localLangArguments
     * @return void
     */
    public function setLocalLangArguments(array $localLangArguments): void
    {
        $this->localLangArguments = serialize($localLangArguments);
    }
}
