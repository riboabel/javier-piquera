<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/19/2020
 * Time: 7:13 p.m.
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class HostingInvoice
 *
 * @ORM\Entity
 * @ORM\Table(name="hosting_invoice", uniqueConstraints={@ORM\UniqueConstraint(columns={"invoice_number"})})
 */
class HostingInvoice
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
     * @var string
     *
     * @ORM\Column(name="invoice_number", length=20)
     */
    private $invoiceNumber;

    /**
     * @var HostingInvoiceProvider
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\HostingInvoiceProvider")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $provider;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $providerName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="HostingInvoiceLine", mappedBy="invoice", cascade={"persist"})
     */
    private $lines;

    /**
     * @var float
     *
     * @ORM\Column(name="grand_total", type="decimal", precision=10, scale=2)
     */
    private $grandTotal;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lines = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return HostingInvoice
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
     * Set invoiceNumber
     *
     * @param string $invoiceNumber
     *
     * @return HostingInvoice
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    /**
     * Get invoiceNumber
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * Set providerName
     *
     * @param string $providerName
     *
     * @return HostingInvoice
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Get providerName
     *
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return HostingInvoice
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add line
     *
     * @param \AppBundle\Entity\HostingInvoiceLine $line
     *
     * @return HostingInvoice
     */
    public function addLine(\AppBundle\Entity\HostingInvoiceLine $line)
    {
        if (!$this->lines->contains($line)) {
            $this->lines[] = $line;
            $line->setInvoice($this);
        }

        return $this;
    }

    /**
     * Remove line
     *
     * @param \AppBundle\Entity\HostingInvoiceLine $line
     */
    public function removeLine(\AppBundle\Entity\HostingInvoiceLine $line)
    {
        $this->lines->removeElement($line);
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

    /**
     * Set grandTotal
     *
     * @param string $grandTotal
     *
     * @return HostingInvoice
     */
    public function setGrandTotal($grandTotal)
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    /**
     * Get grandTotal
     *
     * @return string
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * Set provider
     *
     * @param \AppBundle\Entity\HostingInvoiceProvider $provider
     *
     * @return HostingInvoice
     */
    public function setProvider(\AppBundle\Entity\HostingInvoiceProvider $provider)
    {
        $this->provider = $provider;

        $this->setProviderName($provider->getName());

        return $this;
    }

    /**
     * Get provider
     *
     * @return \AppBundle\Entity\HostingInvoiceProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
