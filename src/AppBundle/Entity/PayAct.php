<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PayAct
 *
 * @ORM\Table(name="pay_act")
 * @ORM\Entity
 */
class PayAct
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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Reserva", mappedBy="payAct")
     */
    private $charges;

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
     * @return ChargeAct
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
     * Constructor
     */
    public function __construct()
    {
        $this->charges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add charges
     *
     * @param \AppBundle\Entity\Reserva $charges
     * @return ChargeAct
     */
    public function addCharge(\AppBundle\Entity\Reserva $charges)
    {
        $this->charges[] = $charges;

        return $this;
    }

    /**
     * Remove charges
     *
     * @param \AppBundle\Entity\Reserva $charges
     */
    public function removeCharge(\AppBundle\Entity\Reserva $charges)
    {
        $this->charges->removeElement($charges);
    }

    /**
     * Get charges
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCharges()
    {
        return $this->charges;
    }
}
