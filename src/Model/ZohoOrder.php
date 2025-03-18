<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;
use XLite\Model\AEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoho_orders", indexes={
 *          @ORM\Index (name="order_id", columns={"order_id"}),
 *          @ORM\Index(name="zoho_id", columns={"zoho_id"})
 *  }
 * )
 */
class ZohoOrder extends AEntity
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
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $zoho_quote_id;

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

    public function getZohoQuoteId()
    {
        return $this->zoho_quote_id;
    }

    public function setZohoQuoteId($zoho_quote_id): self
    {
        $this->zoho_quote_id = $zoho_quote_id;
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