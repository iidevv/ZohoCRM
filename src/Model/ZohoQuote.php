<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;
use XLite\Model\AEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoho_quotes", indexes={
 *          @ORM\Index (name="order_id", columns={"order_id"}),
 *          @ORM\Index(name="zoho_id", columns={"zoho_id"})
 *  }
 * )
 */
class ZohoQuote extends AEntity
{
    /**
     * @var \XLite\Model\Order
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="XLite\Model\Order", cascade={"merge", "detach", "persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", onDelete="CASCADE")
     */
    protected $order_id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $zoho_id;

    /**
     * @var float
     *
     * @ORM\Column (type="decimal", precision=14, scale=4)
     */
    protected $total = 0.0000;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $synced = true;
    
    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $skipped = false;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $errors = '';

    public function getId()
    {
        return $this->order_id;
    }

    public function setId($id)
    {
        $this->order_id = $id;
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

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total): self
    {
        $this->total = $total;
        return $this;
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
}