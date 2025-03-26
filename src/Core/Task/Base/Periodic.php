<?php

namespace Iidev\ZohoCRM\Core\Task\Base;

/**
 * Abstract periodic task
 */
abstract class Periodic extends \Iidev\ZohoCRM\Core\Task\ATask
{
    public const INT_1_MIN     = 60;
    public const INT_4_MIN     = 240;
    public const INT_5_MIN     = 300;
    public const INT_10_MIN    = 600;
    public const INT_15_MIN    = 900;

    /**
     * Get period (seconds)
     *
     * @return integer
     */
    abstract protected function getPeriod();

    /**
     * Finalize step
     *
     * @return void
     */
    protected function finalizeStep()
    {
        parent::finalizeStep();

        $this->release();

        $this->model = \XLite\Core\Database::getEM()->merge($this->model);
        $this->model->setTriggerTime($this->startTime + $this->getPeriod());
    }
}
