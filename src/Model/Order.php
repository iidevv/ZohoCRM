<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
 * @ORM\HasLifecycleCallbacks
 */
class Order extends \XLite\Model\Order implements ZohoAwareInterface
{
    /**
     * @var \Iidev\ZohoCRM\Model\ZohoOrder
     *
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoOrder", mappedBy="order_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoModel;

    /**
     * @var \Iidev\ZohoCRM\Model\ZohoQuote
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoQuote", mappedBy="order_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoQuote;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoOrder|null
     */
    public function getZohoModel()
    {
        return $this->zohoModel;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoOrder|null $zohoModel
     * @return self
     */
    public function setZohoModel($zohoModel): self
    {
        if ($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoQuote) {
            $this->zohoQuote = $zohoModel;
        } else if ($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoDeal) {
            $this->zohoDeal = $zohoModel;
        } else {
            $this->zohoModel = $zohoModel;
        }

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
     * @return array|null
     */
    public function getDecodedZohoErrors()
    {
        $errors = [];
        if ($this->zohoModel && $this->zohoModel->getErrors()) {
            $errors['order'] = json_decode($this->zohoModel->getErrors(), true) ?: [];
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
        return (!$this->zohoModel || $this->zohoModel->getSkipped()) && (!$this->zohoQuote || $this->zohoQuote->getSkipped());
    }

    /**
     * @ORM\PostUpdate
     *
     * @return void
     */
    public function processPostUpdate()
    {
        parent::processPostUpdate();

        if ($this->zohoModel) {
            $this->zohoModel->setSynced(false);
        }
        if ($this->zohoQuote) {
            $this->zohoQuote->setSynced(false);
        }
    }

    public function setOrderNumber($orderNumber)
    {
        if ($this->zohoDeal) {
            $this->zohoDeal->setClosedWon(true);
        }
        
        return parent::setOrderNumber($orderNumber);
    }

    public function setLastVisitDate($lastVisitDate)
    {
        if ($this->zohoDeal) {
            $this->zohoDeal->setSynced(false);
        }

        return parent::setLastVisitDate($lastVisitDate);
    }
}
