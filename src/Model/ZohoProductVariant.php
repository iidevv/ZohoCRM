<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;
use XLite\Model\AEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoho_product_variants", indexes={
 *          @ORM\Index (name="id", columns={"id"}),
 *          @ORM\Index(name="zoho_id", columns={"zoho_id"})
 *  }
 * )
 */
class ZohoProductVariant extends AEntity
{
    /**
     * @var \XC\ProductVariants\Model\ProductVariant
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="XC\ProductVariants\Model\ProductVariant", cascade={"merge", "detach", "persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $zoho_id;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $skipped = false;

    /**
     * @var string
     * @ORM\Column (type="integer", options={ "unsigned": true })
     */
    protected $last_synced = 0;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $errors = '';

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getZohoId()
    {
        return $this->zoho_id;
    }

    public function setZohoId($zoho_id): self
    {
        $this->zoho_id = $zoho_id;
        return $this;
    }

    public function getSkipped()
    {
        return $this->skipped;
    }

    public function setSkipped($skipped): self
    {
        $this->skipped = $skipped;
        return $this;
    }

    public function setErrors($errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getLastSynced()
    {
        return $this->last_synced;
    }

    public function setLastSynced($last_synced): self
    {
        $this->last_synced = $last_synced;

        return $this;
    }
}