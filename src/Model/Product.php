<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Product extends \XLite\Model\Product
{
    /**
     * Zoho CRM ID
     *
     * @var string
     * @ORM\Column (type="string", nullable=true)
     */
    protected $zoho_id;

    /**
     * Get zoho CRM ID
     */
    public function getZohoId(): string
    {
        return $this->zoho_id;
    }

    /**
     * Set zoho CRM ID
     */
    public function setZohoId(string $zoho_id): self
    {
        $this->zoho_id = $zoho_id;

        return $this;
    }
}
