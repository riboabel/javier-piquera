<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ThirdPayAct
 *
 * @ORM\Table(name="third_pay_act")
 * @ORM\Entity
 */
class ThirdPayAct
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReservaTercero", mappedBy="payAct")
     */
    private $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ThirdPayAct
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
     * Add service
     *
     * @param \AppBundle\Entity\ReservaTercero $service
     *
     * @return ThirdPayAct
     */
    public function addService(\AppBundle\Entity\ReservaTercero $service)
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setPayAct($this);
        }

        return $this;
    }

    /**
     * Remove service
     *
     * @param \AppBundle\Entity\ReservaTercero $service
     */
    public function removeService(\AppBundle\Entity\ReservaTercero $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }
}
