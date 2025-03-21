<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
 * 
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
        $this->zohoModel = $zohoModel;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDecodedZohoErrors()
    {
        if ($this->zohoModel && $this->zohoModel->getErrors()) {
            return json_decode($this->zohoModel->getErrors(), true) ?: [];
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isSkipped()
    {
        if ($this->zohoModel) {
            return $this->zohoModel->getSkipped();
        }

        return true;
    }

    /**
     * @ORM\PostUpdate
     *
     * @return void
     */
    public function processPostUpdate()
    {
        if ($this->zohoModel) {
            $this->zohoModel->setSynced(false);
        }

        if ($this->getZohoModel()?->getZohoQuoteId()) {
            $this->zohoModel->setQuoteSynced(false);
        }
    }
}
