<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Order extends \XLite\Model\Order
{
    /**
     * @var string
     * @ORM\Column (type="string", nullable=true)
     */
    protected $zoho_id;

    public function getZohoId()
    {
        return $this->zoho_id;
    }

    public function setZohoId($zoho_id): self
    {
        $this->zoho_id = $zoho_id;

        return $this;
    }
}
