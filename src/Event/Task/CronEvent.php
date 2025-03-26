<?php

declare(strict_types=1);

namespace Iidev\ZohoCRM\Event\Task;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class CronEvent extends Event
{
    public const NAME = 'zoho.cronjob';

    /**
     * Time limit (seconds)
     *
     * @var integer
     */
    protected $timeLimit = 600;

    /**
     * Memory limit (bytes)
     *
     * @var integer
     */
    protected $memoryLimit = 4000000;

    /**
     * Memory limit from memory_limit PHP setting (bytes)
     *
     * @var integer
     */
    protected $memoryLimitIni;

    /**
     * Sleep time
     *
     * @var integer
     */
    protected $sleepTime = 3;

    /**
     * Start time
     *
     * @var integer
     */
    protected $startTime;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @return int
     */
    public function getTimeLimit(): int
    {
        return $this->timeLimit;
    }

    /**
     * @param int $timeLimit
     */
    public function setTimeLimit(int $timeLimit): void
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * @return int
     */
    public function getMemoryLimit(): int
    {
        return $this->memoryLimit;
    }

    /**
     * @param int $memoryLimit
     */
    public function setMemoryLimit(int $memoryLimit): void
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * @return int
     */
    public function getMemoryLimitIni(): int
    {
        return $this->memoryLimitIni;
    }

    /**
     * @param int $memoryLimitIni
     */
    public function setMemoryLimitIni(int $memoryLimitIni): void
    {
        $this->memoryLimitIni = $memoryLimitIni;
    }

    /**
     * @return int
     */
    public function getSleepTime(): int
    {
        return $this->sleepTime;
    }

    /**
     * @param int $sleepTime
     */
    public function setSleepTime(int $sleepTime): void
    {
        $this->sleepTime = $sleepTime;
    }

    /**
     * @return int
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }

    /**
     * @param int $startTime
     */
    public function setStartTime(int $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
