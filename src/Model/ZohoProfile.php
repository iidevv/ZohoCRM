<?php

namespace Iidev\ZohoCRM\Model;

use Doctrine\ORM\Mapping as ORM;
use XLite\Model\AEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoho_profiles", indexes={@ORM\Index(name="zoho_id", columns={"zoho_id"})})
 */
class ZohoProfile extends AEntity
{
    /**
     * @var \XLite\Model\Profile
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="XLite\Model\Profile", cascade={"merge", "detach", "persist"})
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="profile_id", onDelete="CASCADE")
     */
    protected $profile_id;

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
     * @ORM\Column(type="text")
     */
    protected $errors = '';

    public function getId()
    {
        return $this->profile_id;
    }

    public function setId($id)
    {
        $this->profile_id = $id;
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
}