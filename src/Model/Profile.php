<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;

/**
 * @Extender\Mixin
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
}
