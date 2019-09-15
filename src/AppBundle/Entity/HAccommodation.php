<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/13/2019
 * Time: 5:09 PM
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class HAccommodation
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HAccommodationRepository")
 * @ORM\Table(name="h_accommodation", indexes={@ORM\Index(columns={"reference"})})
 */
class HAccommodation
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
     * @ORM\Column(name="start_date", type="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date")
     */
    private $endDate;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(1)
     */
    private $nights;

    /**
     * @var string
     *
     * @ORM\Column(length=20)
     * @Assert\NotBlank
     * @Assert\Length(max="20")
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $leadClient;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(0)
     */
    private $pax;

    /**
     * @var HProvider
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\HProvider")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $provider;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $cost;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max="32000")
     */
    private $details;

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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return HAccommodation
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return HAccommodation
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set nights
     *
     * @param integer $nights
     *
     * @return HAccommodation
     */
    public function setNights($nights)
    {
        $this->nights = $nights;

        return $this;
    }

    /**
     * Get nights
     *
     * @return integer
     */
    public function getNights()
    {
        return $this->nights;
    }

    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return HAccommodation
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set leadClient
     *
     * @param string $leadClient
     *
     * @return HAccommodation
     */
    public function setLeadClient($leadClient)
    {
        $this->leadClient = $leadClient;

        return $this;
    }

    /**
     * Get leadClient
     *
     * @return string
     */
    public function getLeadClient()
    {
        return $this->leadClient;
    }

    /**
     * Set pax
     *
     * @param integer $pax
     *
     * @return HAccommodation
     */
    public function setPax($pax)
    {
        $this->pax = $pax;

        return $this;
    }

    /**
     * Get pax
     *
     * @return integer
     */
    public function getPax()
    {
        return $this->pax;
    }

    /**
     * Set cost
     *
     * @param string $cost
     *
     * @return HAccommodation
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set details
     *
     * @param string $details
     *
     * @return HAccommodation
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set provider
     *
     * @param \AppBundle\Entity\HProvider $provider
     *
     * @return HAccommodation
     */
    public function setProvider(\AppBundle\Entity\HProvider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \AppBundle\Entity\HProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
