<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ProductVariant extends \XC\ProductVariants\Model\ProductVariant
{
    /**
     *
     * @var string
     * @ORM\Column (type="string", nullable=true)
     */
    protected $zoho_id;

    public function getZohoId(): string
    {
        return $this->zoho_id;
    }

    public function setZohoId(string $zoho_id): self
    {
        $this->zoho_id = $zoho_id;

        return $this;
    }
}
