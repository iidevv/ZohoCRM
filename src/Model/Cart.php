<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;
use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 * @ORM\HasLifecycleCallbacks
 */
class Cart extends \XLite\Model\Cart
{
    /**
     * @var \Iidev\ZohoCRM\Model\zohoDeal
     * @ORM\OneToOne(targetEntity="Iidev\ZohoCRM\Model\zohoDeal", mappedBy="order_id", cascade={"merge", "detach", "persist"})
     */
    protected $zohoDeal;

    /**
     * @return \Iidev\ZohoCRM\Model\ZohoDeal|null
     */
    public function getZohoDeal()
    {
        return $this->zohoDeal;
    }

    /**
     * @param \Iidev\ZohoCRM\Model\ZohoDeal|null $zohoDeal
     * @return self
     */
    public function setZohoDeal($zohoDeal): self
    {
        $this->zohoDeal = $zohoDeal;
        return $this;
    }
}
