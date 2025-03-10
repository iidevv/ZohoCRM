<?php

namespace Iidev\ZohoCRM\Command;


use XLite\Core\Lock\FileLock;

trait LockAwareTrait
{
    public function getKey()
    {
        return str_replace(':', '.', static::$defaultName);
    }

    public function isRunning()
    {
        return FileLock::getInstance()->isRunning($this->getKey());
    }

    public function setRunning()
    {
        FileLock::getInstance()->setRunning($this->getKey());
    }

    public function releaseLock()
    {
        FileLock::getInstance()->release($this->getKey());
    }
}