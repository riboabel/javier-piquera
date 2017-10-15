<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Place
 *
 * @ORM\Table(name="place", indexes={@ORM\Index(columns={"name"})})
 * @ORM\Entity
 */
class Place
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_address", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $postalAddress;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $location;

    /**
     * @var Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType
     *
     * @ORM\Column(name="mobile_phone_number", type="phone_number", nullable=true)
     */
    private $mobilePhone;

    /**
     * @var Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType
     *
     * @ORM\Column(name="fixede_phone_number", type="phone_number", nullable=true)
     */
    private $fixedPhone;

    /**
     * @var Enterprise
     *
     * @ORM\ManyToOne(targetEntity="Enterprise")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $enterprise;

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Place
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set postalAddress
     *
     * @param string $postalAddress
     * @return Place
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    /**
     * Get postalAddress
     *
     * @return string
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * Set enterprise
     *
     * @param \AppBundle\Entity\Enterprise $enterprise
     * @return Place
     */
    public function setEnterprise(\AppBundle\Entity\Enterprise $enterprise)
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    /**
     * Get enterprise
     *
     * @return \AppBundle\Entity\Enterprise
     */
    public function getEnterprise()
    {
        return $this->enterprise;
    }

    /**
     * Set location
     *
     * @param \AppBundle\Entity\Location $location
     * @return Place
     */
    public function setLocation(\AppBundle\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \AppBundle\Entity\Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set mobilePhone
     *
     * @param phone_number $mobilePhone
     *
     * @return Place
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    /**
     * Get mobilePhone
     *
     * @return phone_number
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * Set fixedPhone
     *
     * @param phone_number $fixedPhone
     *
     * @return Place
     */
    public function setFixedPhone($fixedPhone)
    {
        $this->fixedPhone = $fixedPhone;

        return $this;
    }

    /**
     * Get fixedPhone
     *
     * @return phone_number
     */
    public function getFixedPhone()
    {
        return $this->fixedPhone;
    }
}
