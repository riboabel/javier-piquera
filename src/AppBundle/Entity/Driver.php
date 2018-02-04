<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Driver
 *
 * @ORM\Table(name="car_driver")
 * @ORM\Entity
 */
class Driver
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
     * @ORM\Column(name="name", type="string", length=500)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="car_indicator", type="string", length=10, nullable=true)
     */
    private $carIndicator;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_info", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $contactInfo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_dreiver_guide", type="boolean", options={"default": false})
     */
    private $isDriverGuide;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default": true})
     */
    private $enabled;

    /**
     * @var Enterprise
     *
     * @ORM\ManyToOne(targetEntity="Enterprise")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $enterprise;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_address", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $postalAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account", type="string", length=255, nullable=true)
     */
    private $bankAccount;

    /**
     * @var string
     *
     * @ORM\Column(name="nit", type="string", length=20, nullable=true)
     */
    private $nit;

    /**
     * @var PhoneNumberType
     *
     * @ORM\Column(name="mobile_phone_number", type="phone_number", nullable=true)
     */
    private $mobilePhone;

    /**
     * @var PhoneNumberType
     *
     * @ORM\Column(name="fixede_phone_number", type="phone_number", nullable=true)
     */
    private $fixedPhone;

    public function __construct()
    {
        $this->isDriverGuide = false;
        $this->enabled = true;
    }

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
     * @return Driver
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
     * Set contactInfo
     *
     * @param string $contactInfo
     * @return Driver
     */
    public function setContactInfo($contactInfo)
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    /**
     * Get contactInfo
     *
     * @return string
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Driver
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set isDriverGuide
     *
     * @param boolean $isDriverGuide
     * @return Driver
     */
    public function setIsDriverGuide($isDriverGuide)
    {
        $this->isDriverGuide = $isDriverGuide;

        return $this;
    }

    /**
     * Get isDriverGuide
     *
     * @return boolean
     */
    public function getIsDriverGuide()
    {
        return $this->isDriverGuide;
    }

    /**
     * Set enterprise
     *
     * @param \AppBundle\Entity\Enterprise $enterprise
     * @return Driver
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
     * Set carIndicator
     *
     * @param string $carIndicator
     * @return Driver
     */
    public function setCarIndicator($carIndicator)
    {
        $this->carIndicator = $carIndicator;

        return $this;
    }

    /**
     * Get carIndicator
     *
     * @return string
     */
    public function getCarIndicator()
    {
        return $this->carIndicator;
    }

    /**
     * Set postalAddress
     *
     * @param string $postalAddress
     * @return Driver
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
     * Set bankAccount
     *
     * @param string $bankAccount
     * @return Driver
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set nit
     *
     * @param string $nit
     * @return Driver
     */
    public function setNit($nit)
    {
        $this->nit = $nit;

        return $this;
    }

    /**
     * Get nit
     *
     * @return string
     */
    public function getNit()
    {
        return $this->nit;
    }

    /**
     * Set mobilePhone
     *
     * @param phone_number $mobilePhone
     * @return Driver
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
     * @return Driver
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
