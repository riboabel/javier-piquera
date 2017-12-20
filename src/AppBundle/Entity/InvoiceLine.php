<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * InvoiceLine
 *
 * @ORM\Table(name="invoice_line")
 * @ORM\Entity
 */
class InvoiceLine
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
     * @var Invoice
     *
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="lines")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="service_name")
     * @Assert\Length(max=255)
     */
    private $serviceName;

    /**
     * @var string
     *
     * @ORM\Column(name="clients_name", type="text", nullable=true)
     * @Assert\Length(max=255)
     */
    private $clientsName;

    /**
     * @var string
     *
     * @ORM\Column(name="client_reference", length=50, nullable=true)
     * @Assert\Length(max=50)
     */
    private $clientReference;

    /**
     * @var string
     *
     * @ORM\Column(name="service_serial_number", length=20)
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    private $serviceSerialNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="meassurement_unit", type="string", length=49, nullable=true)
     */
    private $meassurementUnit;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $unitPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="total_price", type="decimal", precision=10, scale=2)
     */
    private $totalPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;


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
     * Set meassurementUnit
     *
     * @param string $meassurementUnit
     * @return InvoiceLine
     */
    public function setMeassurementUnit($meassurementUnit)
    {
        $this->meassurementUnit = $meassurementUnit;

        return $this;
    }

    /**
     * Get meassurementUnit
     *
     * @return string
     */
    public function getMeassurementUnit()
    {
        return $this->meassurementUnit;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return InvoiceLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set unitPrice
     *
     * @param string $unitPrice
     * @return InvoiceLine
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * Get unitPrice
     *
     * @return string
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Set totalPrice
     *
     * @param string $totalPrice
     * @return InvoiceLine
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return InvoiceLine
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
     * Set invoice
     *
     * @param \AppBundle\Entity\Invoice $invoice
     * @return InvoiceLine
     */
    public function setInvoice(\AppBundle\Entity\Invoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return \AppBundle\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set serviceName
     *
     * @param string $serviceName
     * @return InvoiceLine
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    /**
     * Get serviceName
     *
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * Set clientsName
     *
     * @param string $clientsName
     * @return InvoiceLine
     */
    public function setClientsName($clientsName)
    {
        $this->clientsName = $clientsName;

        return $this;
    }

    /**
     * Get clientsName
     *
     * @return string
     */
    public function getClientsName()
    {
        return $this->clientsName;
    }

    /**
     * Set clientReference
     *
     * @param string $clientReference
     * @return InvoiceLine
     */
    public function setClientReference($clientReference)
    {
        $this->clientReference = $clientReference;

        return $this;
    }

    /**
     * Get clientReference
     *
     * @return string
     */
    public function getClientReference()
    {
        return $this->clientReference;
    }

    /**
     * Set serviceSerialNumber
     *
     * @param string $serviceSerialNumber
     * @return InvoiceLine
     */
    public function setServiceSerialNumber($serviceSerialNumber)
    {
        $this->serviceSerialNumber = $serviceSerialNumber;

        return $this;
    }

    /**
     * Get serviceSerialNumber
     *
     * @return string
     */
    public function getServiceSerialNumber()
    {
        return $this->serviceSerialNumber;
    }
}
