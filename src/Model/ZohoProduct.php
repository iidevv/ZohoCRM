<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;
use XLite\Model\AEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoho_products", indexes={
 *          @ORM\Index (name="product_id", columns={"product_id"}),
 *          @ORM\Index(name="zoho_id", columns={"zoho_id"})
 *  }
 * )
 */
class ZohoProduct extends AEntity
{
    /**
     * @var \XLite\Model\Product
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="XLite\Model\Product", cascade={"merge", "detach", "persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE")
     */
    protected $product_id;

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
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $synced = true;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $errors = '';

    public function getId()
    {
        return $this->product_id;
    }

    public function setId($id)
    {
        $this->product_id = $id;
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

    public function getSynced()
    {
        return $this->synced;
    }

    public function setSynced($synced): self
    {
        $this->synced = $synced;
        return $this;
    }
}