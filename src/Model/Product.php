<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
 * @ORM\HasLifecycleCallbacks
 */
class Product extends \XLite\Model\Product implements ZohoAwareInterface
{
    /**
     * @var \Iidev\ZohoCRM\Model\ZohoProduct
     *
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoProduct", mappedBy="product_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoModel;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoProduct|null
     */
    public function getZohoModel()
    {
        return $this->zohoModel;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoProduct|null $zohoModel
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

        $changeSet = \XLite\Core\Database::getEM()->getUnitOfWork()->getEntityChangeSet($this);

        if (isset($changeSet['price']) && $this->hasVariants()) {
            $this->setZohoVariantsNotSynced($this->getVariants());
        } else if ($this->zohoModel) {
            $this->zohoModel->setSynced(false);
        }
    }

    protected function setZohoVariantsNotSynced($variants)
    {
        foreach ($variants as $variant) {
            if (!$variant->getZohoModel())
                return;

            $variant->getZohoModel()->setSynced(false);
        }
    }
}
