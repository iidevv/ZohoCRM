<?php

namespace Iidev\ZohoCRM\Core\Task;

use Symfony\Component\Console\Output\OutputInterface;
use Iidev\ZohoCRM\Event\Task\CronEvent;

/**
 * Abstract task
 */
abstract class ATask extends \XLite\Base
{
    /**
     * Model
     *
     * @var \XLite\Model\Task
     */
    protected $model;

    /**
     * Start time
     *
     * @var integer
     */
    protected $startTime;

    /**
     * Last step flag
     *
     * @var boolean
     */
    protected $lastStep = false;

    /**
     * Result operation message
     *
     * @var string
     */
    protected $message;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Get title
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Run step
     */
    abstract protected function runStep();

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function handleTask(CronEvent $event): void
    {
        $this->model = \XLite\Core\Database::getRepo('XLite\Model\Task')->findOneBy(['owner' => static::class]);

        if (!$this->model) {
            $this->model = new \XLite\Model\Task();
            $this->model->setOwner(static::class);
            $this->model->setTriggerTime(0);
            \XLite\Core\Database::getEM()->persist($this->model);
            \XLite\Core\Database::getEM()->flush();
        }

        $this->startTime = $event->getStartTime();
        $this->output = $event->getOutput();

        if (!$this->model->isExpired()) {
            return;
        }

        $this->runRunner();

        sleep($event->getSleepTime());

        if (!$this->checkThreadResource($event)) {
            $time = gmdate('H:i:s', \XLite\Core\Converter::time() - $this->startTime);
            $memory = \XLite\Core\Converter::formatFileSize(memory_get_usage(true));
            $this->printContent('Step is interrupted (time: ' . $time . '; memory usage: ' . $memory . ')');

            $event->stopPropagation();
        }
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Check - task ready or not
     *
     * @return boolean
     */
    public function isReady()
    {
        return true;
    }

    /**
     * Should task started if previous attempt has failed
     *
     * @return boolean
     */
    public function shouldRunIfCrashed()
    {
        return true;
    }

    /**
     * Lock key
     *
     * @return string
     */
    public function getLockKey()
    {
        return static::class . $this->model->getId();
    }

    /**
     * Check - task ready or not
     *
     * @return boolean
     */
    public function isRunning()
    {
        return \XLite\Core\Lock\FileLock::getInstance()->isRunning(
            $this->getLockKey(),
            !$this->shouldRunIfCrashed()
        );
    }

    /**
     * Mark task as running
     *
     * @return void
     */
    protected function markAsRunning()
    {
        \XLite\Core\Lock\FileLock::getInstance()->setRunning(
            $this->getLockKey()
        );
    }

    /**
     * mark as not running
     *
     * @return void
     */
    protected function release()
    {
        \XLite\Core\Lock\FileLock::getInstance()->release(
            $this->getLockKey()
        );
    }

    /**
     * @return void
     */
    protected function runRunner()
    {
        $silence = !$this->getTitle();
        if ($this->isReady() && !$this->isRunning()) {
            if (!$silence) {
                $this->printContent($this->getTitle() . ' ... ');
            }

            $this->run();

            if (!$silence) {
                $this->printContent($this->getMessage() ?: 'done');
            }
        } elseif ($this->isRunning()) {
            $msg = !$this->shouldRunIfCrashed()
                ? '| Task will not be restarted because previous attempt has failed. Remove lock files manually to start the task'
                : '';
            $this->printContent($this->getTitle() . ' ... Already running ' . $msg);
        }

        if (!$silence) {
            $this->printContent(PHP_EOL);
        }

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Run task
     */
    public function run()
    {
        if ($this->isValid()) {
            $this->prepareStep();

            $this->markAsRunning();

            $this->runStep();

            if ($this->isLastStep()) {
                $this->finalizeTask();
            } else {
                $this->finalizeStep();
            }
        } elseif (!$this->message) {
            $this->message = 'invalid';
        }
    }

    /**
     * Check thread resource
     *
     * @return boolean
     */
    protected function checkThreadResource($event)
    {
        return time() - $event->getStartTime() < $event->getTimeLimit()
            && $event->getMemoryLimitIni() - memory_get_usage(true) > $event->getMemoryLimit();
    }

    /**
     * Print content
     *
     * @param string $str Content
     *
     * @return void
     */
    protected function printContent($str)
    {
        if (\XLite\Core\Request::getInstance()->isCLI()) {
            $this->output->write($str);
        }
    }

    /**
     * Prepare step
     *
     * @return void
     */
    protected function prepareStep()
    {
    }

    /**
     * Check - current step is last or not
     *
     * @return boolean
     */
    protected function isLastStep()
    {
        return $this->lastStep;
    }

    /**
     * Finalize task (last step)
     */
    protected function finalizeTask()
    {
        $this->release();
        $this->close();
    }

    /**
     * Finalize step
     */
    protected function finalizeStep()
    {
    }

    /**
     * Check availability
     *
     * @return boolean
     */
    protected function isValid()
    {
        return true;
    }

    /**
     * Close task
     */
    protected function close()
    {
        \XLite\Core\Database::getEM()->remove($this->model);
    }
}
