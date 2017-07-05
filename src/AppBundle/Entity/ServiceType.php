<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceType
 *
 * @ORM\Table(name="service_type")
 * @ORM\Entity
 */
class ServiceType
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
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_multiple", type="boolean")
     */
    private $isMultiple;

    /**
     * @var string
     *
     * @ORM\Column(name="default_price", type="decimal", scale=2, options={"default": 0})
     */
    private $defaultPrice = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="default_pay_amount", type="decimal", scale=2, options={"default": 0})
     */
    private $defaultPayAmount = 0;

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
     * @return ServiceType
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
     * Set isMultiple
     *
     * @param boolean $isMultiple
     * @return ServiceType
     */
    public function setIsMultiple($isMultiple)
    {
        $this->isMultiple = $isMultiple;

        return $this;
    }

    /**
     * Get isMultiple
     *
     * @return boolean
     */
    public function getIsMultiple()
    {
        return $this->isMultiple;
    }

    /**
     * Set defaultPrice
     *
     * @param string $defaultPrice
     * @return ServiceType
     */
    public function setDefaultPrice($defaultPrice)
    {
        $this->defaultPrice = $defaultPrice;

        return $this;
    }

    /**
     * Get defaultPrice
     *
     * @return string
     */
    public function getDefaultPrice()
    {
        return $this->defaultPrice;
    }

    /**
     * Set defaultPayAmount
     *
     * @param string $defaultPayAmount
     * @return ServiceType
     */
    public function setDefaultPayAmount($defaultPayAmount)
    {
        $this->defaultPayAmount = $defaultPayAmount;

        return $this;
    }

    /**
     * Get defaultPayAmount
     *
     * @return string 
     */
    public function getDefaultPayAmount()
    {
        return $this->defaultPayAmount;
    }

    /**
     * Set enterprise
     *
     * @param \AppBundle\Entity\Enterprise $enterprise
     * @return ServiceType
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
}
