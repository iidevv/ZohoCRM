<?php


namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
 */
class Message extends \XC\VendorMessages\Model\Message implements ZohoAwareInterface
{
    /**
     * @var \Iidev\ZohoCRM\Model\ZohoOrderMessage
     *
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoOrderMessage", mappedBy="id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoModel;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoOrderMessage|null
     */
    public function getZohoModel()
    {
        return $this->zohoModel;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoOrderMessage|null $zohoModel
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
}