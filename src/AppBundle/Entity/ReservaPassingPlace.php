<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReservaPassingPlace
 *
 * @ORM\Table(name="reserva_passing_place")
 * @ORM\Entity
 */
class ReservaPassingPlace
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
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $place;

    /**
     * @var Resserva
     *
     * @ORM\ManyToOne(targetEntity="Reserva", inversedBy="passingPlaces")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $reserva;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stay_at", type="date")
     */
    private $stayAt;


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
     * Set stayAt
     *
     * @param \DateTime $stayAt
     * @return ReservaPassingPlace
     */
    public function setStayAt($stayAt)
    {
        $this->stayAt = $stayAt;

        return $this;
    }

    /**
     * Get stayAt
     *
     * @return \DateTime 
     */
    public function getStayAt()
    {
        return $this->stayAt;
    }

    /**
     * Set place
     *
     * @param \AppBundle\Entity\Place $place
     * @return ReservaPassingPlace
     */
    public function setPlace(\AppBundle\Entity\Place $place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \AppBundle\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set reserva
     *
     * @param \AppBundle\Entity\Reserva $reserva
     * @return ReservaPassingPlace
     */
    public function setReserva(\AppBundle\Entity\Reserva $reserva)
    {
        $this->reserva = $reserva;

        return $this;
    }

    /**
     * Get reserva
     *
     * @return \AppBundle\Entity\Reserva 
     */
    public function getReserva()
    {
        return $this->reserva;
    }
}
