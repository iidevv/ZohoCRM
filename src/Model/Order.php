<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 * @ORM\HasLifecycleCallbacks
 */
class Order extends \XLite\Model\Order
{
    /**
     * @var \Iidev\ZohoCRM\Model\ZohoOrder
     *
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoOrder", mappedBy="order_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoOrder;

    /**
     * @var \Iidev\ZohoCRM\Model\ZohoQuote
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoQuote", mappedBy="order_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoQuote;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoOrder|null
     */
    public function getZohoOrder()
    {
        return $this->zohoOrder;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoOrder|null $zohoOrder
     * @return self
     */
    public function setZohoOrder($zohoOrder): self
    {
        $this->zohoOrder = $zohoOrder;
        return $this;
    }

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoQuote|null
     */
    public function getZohoQuote()
    {
        return $this->zohoQuote;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoQuote|null $zohoQuote
     * @return self
     */
    public function setZohoQuote($zohoQuote): self
    {
        $this->zohoQuote = $zohoQuote;
        return $this;
    }

    /**
     * @param mixed $zohoModel
     * @return self
     */
    public function setZohoModel($zohoModel): self
    {
        if ($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoOrder) {
            $this->zohoOrder = $zohoModel;
        } elseif ($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoQuote) {
            $this->zohoQuote = $zohoModel;
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDecodedZohoErrors()
    {
        $errors = [];
        if ($this->zohoOrder && $this->zohoOrder->getErrors()) {
            $errors['order'] = json_decode($this->zohoOrder->getErrors(), true) ?: [];
        }
        if ($this->zohoQuote && $this->zohoQuote->getErrors()) {
            $errors['quote'] = json_decode($this->zohoQuote->getErrors(), true) ?: [];
        }
        return $errors ?: null;
    }

    /**
     * @return bool
     */
    public function isSkipped()
    {
        return (!$this->zohoOrder || $this->zohoOrder->getSkipped()) && (!$this->zohoQuote || $this->zohoQuote->getSkipped());
    }

    /**
     * @ORM\PostUpdate
     *
     * @return void
     */
    public function processPostUpdate()
    {
        parent::processPostUpdate();

        if ($this->zohoOrder) {
            $this->zohoOrder->setSynced(false);
        }
        if ($this->zohoQuote) {
            $this->zohoQuote->setSynced(false);
        }
    }
}
