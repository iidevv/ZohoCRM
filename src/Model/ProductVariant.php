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

    /**
     * @var string
     * @ORM\Column (type="integer", options={ "unsigned": true })
     */
    protected $zoho_last_synced = 0;

    public function getZohoId()
    {
        return $this->zoho_id;
    }

    public function setZohoId($zoho_id): self
    {
        $this->zoho_id = $zoho_id;

        return $this;
    }

    public function getZohoLastSynced()
    {
        return $this->zoho_last_synced;
    }

    public function setZohoLastSynced($zoho_last_synced): self
    {
        $this->zoho_last_synced = $zoho_last_synced;

        return $this;
    }
}
