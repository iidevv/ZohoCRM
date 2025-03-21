<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
 * @ORM\HasLifecycleCallbacks
 */
class Profile extends \XLite\Model\Profile implements ZohoAwareInterface
{
    /**
     * @var \Iidev\ZohoCRM\Model\ZohoProfile
     *
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\ZohoProfile", mappedBy="profile_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoModel;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoProfile|null
     */
    public function getZohoModel()
    {
        return $this->zohoModel;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoProfile|null $zohoModel
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
        $changeSet = \XLite\Core\Database::getEM()->getUnitOfWork()->getEntityChangeSet($this);
        
        if (!isset($changeSet['login']) && !isset($changeSet['membership'])) {
            return;
        }

        if ($this->zohoModel) {
            $this->zohoModel->setSynced(false);
        }
    }
}
