<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/19/2020
 * Time: 7:31 p.m.
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HostingInvoiceLine
 *
 * @ORM\Entity
 * @ORM\Table(name="hosting_invoice_line")
 */
class HostingInvoiceLine
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
     * @var string
     *
     * @ORM\Column(name="booking_reference", length=40)
     */
    private $bookingReference;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $service;

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
     */
    private $nights;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $clientName;

    /**
     * @var string
     *
     * @ORM\Column(name="row_total", type="decimal", scale=2, precision=10)
     */
    private $rowTotal;

    /**
     * @var HostingInvoice
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\HostingInvoice", inversedBy="lines")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $invoice;

    /**
     * @var integer
     */
    public $accommodationId;

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
     * Set bookingReference
     *
     * @param string $bookingReference
     *
     * @return HostingInvoiceLine
     */
    public function setBookingReference($bookingReference)
    {
        $this->bookingReference = $bookingReference;

        return $this;
    }

    /**
     * Get bookingReference
     *
     * @return string
     */
    public function getBookingReference()
    {
        return $this->bookingReference;
    }

    /**
     * Set service
     *
     * @param string $service
     *
     * @return HostingInvoiceLine
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return HostingInvoiceLine
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
     * @return HostingInvoiceLine
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
     * @return HostingInvoiceLine
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
     * Set clientName
     *
     * @param string $clientName
     *
     * @return HostingInvoiceLine
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Get clientName
     *
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Set invoice
     *
     * @param \AppBundle\Entity\HostingInvoice $invoice
     *
     * @return HostingInvoiceLine
     */
    public function setInvoice(\AppBundle\Entity\HostingInvoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return \AppBundle\Entity\HostingInvoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set rowTotal
     *
     * @param string $rowTotal
     *
     * @return HostingInvoiceLine
     */
    public function setRowTotal($rowTotal)
    {
        $this->rowTotal = $rowTotal;

        return $this;
    }

    /**
     * Get rowTotal
     *
     * @return string
     */
    public function getRowTotal()
    {
        return $this->rowTotal;
    }
}
