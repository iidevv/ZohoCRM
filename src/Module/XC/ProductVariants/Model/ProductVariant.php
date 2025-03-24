<?php

namespace Iidev\ZohoCRM\Module\XC\ProductVariants\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
 * @ORM\HasLifecycleCallbacks
 */
class ProductVariant extends \XC\ProductVariants\Model\ProductVariant implements ZohoAwareInterface
{
    /**
     * @var \Iidev\ZohoCRM\Model\ZohoProductVariant
     *
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoProductVariant", mappedBy="id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoModel;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoProductVariant|null
     */
    public function getZohoModel()
    {
        return $this->zohoModel;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoProductVariant|null $zohoModel
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
     */
    public function processPostUpdate()
    {
        parent::processPostUpdate();

        if ($this->zohoModel) {
            $this->zohoModel->setSynced(false);
        }
    }
}
