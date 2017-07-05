<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * Price
 *
 * @ORM\Table(name="price", uniqueConstraints={@ORM\UniqueConstraint(columns={"service_type_id", "provider_id"})})
 * @ORM\Entity(repositoryClass="PriceRepository")
 */
class Price
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
     * @var Provider
     *
     * @ORM\ManyToOne(targetEntity="Provider")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $provider;

    /**
     * @var ServiceType
     *
     * @ORM\ManyToOne(targetEntity="ServiceType")
     * @ORM\JoinColumn(name="service_type_id", nullable=false, onDelete="cascade")
     */
    private $serviceType;

    /**
     * @var string
     *
     * @ORM\Column(name="payable_charge", type="decimal", scale=2, nullable=true)
     */
    private $payableCharge;

    /**
     * @var string
     *
     * @ORM\Column(name="receivable_charge", type="decimal", scale=2, nullable=true)
     */
    private $receivableCharge;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;


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
     * Set payableCharge
     *
     * @param string $payableCharge
     * @return Price
     */
    public function setPayableCharge($payableCharge)
    {
        $this->payableCharge = $payableCharge;

        return $this;
    }

    /**
     * Get payableCharge
     *
     * @return string
     */
    public function getPayableCharge()
    {
        return $this->payableCharge;
    }

    /**
     * Set receivableCharge
     *
     * @param string $receivableCharge
     * @return Price
     */
    public function setReceivableCharge($receivableCharge)
    {
        $this->receivableCharge = $receivableCharge;

        return $this;
    }

    /**
     * Get receivableCharge
     *
     * @return string
     */
    public function getReceivableCharge()
    {
        return $this->receivableCharge;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Price
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Price
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set provider
     *
     * @param \AppBundle\Entity\Provider $provider
     * @return Price
     */
    public function setProvider(\AppBundle\Entity\Provider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \AppBundle\Entity\Provider 
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set serviceType
     *
     * @param \AppBundle\Entity\ServiceType $serviceType
     * @return Price
     */
    public function setServiceType(\AppBundle\Entity\ServiceType $serviceType)
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * Get serviceType
     *
     * @return \AppBundle\Entity\ServiceType 
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }
}
