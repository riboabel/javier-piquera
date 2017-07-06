<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Invoice
 *
 * @ORM\Table(name="invoice")
 * @ORM\Entity
 */
class Invoice
{
    /**
     * @var int
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
     * @var string
     *
     * @ORM\Column(name="model_name", type="string", length=255)
     */
    private $modelName;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_number")
     */
    private $serialNumber;

    /**
     * @var Provider
     *
     * @ORM\ManyToOne(targetEntity="Provider")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $provider;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="Driver")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $driver;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InvoiceLine", mappedBy="invoice", cascade={"persist", "remove"})
     */
    private $lines;

    /**
     * @var string
     *
     * @ORM\Column(name="total_charge", type="decimal", precision=10, scale=2)
     */
    private $totalCharge;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lines = new ArrayCollection();
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
     * @return Invoice
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
     * Set modelName
     *
     * @param string $modelName
     * @return Invoice
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;

        return $this;
    }

    /**
     * Get modelName
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * Set serialNumber
     *
     * @param string $serialNumber
     * @return Invoice
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber
     *
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * Set totalCharge
     *
     * @param string $totalCharge
     * @return Invoice
     */
    public function setTotalCharge($totalCharge)
    {
        $this->totalCharge = $totalCharge;

        return $this;
    }

    /**
     * Get totalCharge
     *
     * @return string
     */
    public function getTotalCharge()
    {
        return $this->totalCharge;
    }

    /**
     * Set provider
     *
     * @param \AppBundle\Entity\Provider $provider
     * @return Invoice
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
     * Set driver
     *
     * @param \AppBundle\Entity\Driver $driver
     * @return Invoice
     */
    public function setDriver(\AppBundle\Entity\Driver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get driver
     *
     * @return \AppBundle\Entity\Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Add lines
     *
     * @param \AppBundle\Entity\InvoiceLine $line
     * @return Invoice
     */
    public function addLine(\AppBundle\Entity\InvoiceLine $line)
    {
        if (!$this->lines->contains($line)) {
            $this->lines[] = $line;
            $line->setInvoice($this);
        }

        return $this;
    }

    /**
     * Remove lines
     *
     * @param \AppBundle\Entity\InvoiceLine $lines
     */
    public function removeLine(\AppBundle\Entity\InvoiceLine $lines)
    {
        $this->lines->removeElement($lines);
    }

    /**
     * Get lines
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLines()
    {
        return $this->lines;
    }
}
