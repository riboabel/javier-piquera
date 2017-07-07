<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Reserva
 *
 * @ORM\Table(name="reserva",
 *  indexes={
 *      @ORM\Index(columns={"start_at"}),
 *      @ORM\Index(columns={"client_names"}),
 *      @ORM\Index(columns={"provider_reference"}),
 *      @ORM\Index(columns={"invoice_number"})
 *  })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReservaRepository")
 */
class Reserva
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @var TravelGuide
     *
     * @ORM\ManyToOne(targetEntity="TravelGuide")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $guide;

    /**
     * @var string
     *
     * @ORM\Column(name="provider_reference", type="string", length=255, nullable=true)
     */
    private $providerReference;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(name="start_place_id", nullable=false, onDelete="cascade")
     */
    private $startPlace;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(name="end_place_id", nullable=false, onDelete="cascade")
     */
    private $endPlace;

    /**
     * @var ServiceType
     *
     * @ORM\ManyToOne(targetEntity="ServiceType")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $serviceType;

    /**
     * @var string
     *
     * @ORM\Column(name="service_description", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $serviceDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="client_names", type="string", length=500)
     * @Assert\Length(max=500)
     */
    private $clientNames;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pax;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_executed", type="boolean", options={"default": false})
     */
    private $isExecuted = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_cancelled", type="boolean", options={"default": false})
     */
    private $isCancelled = false;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_pay_amount", type="decimal", scale=2)
     */
    private $driverPayAmount = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="paid_at", type="datetime", nullable=true)
     */
    private $paidAt;

    /**
     * @var string
     *
     * @ORM\Column(name="client_price_amount", type="decimal", precision=10, scale=2)
     */
    private $clientPriceAmount = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cobrado_at", type="datetime", nullable=true)
     */
    private $cobradoAt;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="Driver")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $driver;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_driver_confirmed", type="boolean", options={"default": false})
     */
    private $isDriverConfirmed = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReservaPassingPlace", mappedBy="reserva", cascade={"persist", "remove"})
     */
    private $passingPlaces;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=200, nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="updated_by", type="string", length=200, nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    private $updatedBy;

    /**
     * @var Enterprise
     *
     * @ORM\ManyToOne(targetEntity="Enterprise")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $enterprise;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="invoiced_at", type="datetime", nullable=true)
     */
    private $invoicedAt;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="Driver")
     * @ORM\JoinColumn(name="invoice_driver_id", nullable=true, onDelete="set null")
     */
    private $invoiceDriver;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", length=10, nullable=true)
     */
    private $invoiceNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_kilometers", type="decimal", scale=1, precision=6, nullable=true)
     */
    private $invoicedKilometers;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_kilometer_price", type="decimal", scale=2, precision=6, nullable=true)
     */
    private $invoicedKilometerPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_kilometers_price", type="decimal", scale=2, precision=6, nullable=true)
     */
    private $invoicedKilometersPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_hours", type="decimal", scale=1, precision=6, nullable=true)
     */
    private $invoicedHours;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_hour_price", type="decimal", scale=2, precision=6, nullable=true)
     */
    private $invoicedHourPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_hours_price", type="decimal", scale=2, precision=6, nullable=true)
     */
    private $invoicedHoursPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiced_total_price", type="decimal", scale=2, precision=6, nullable=true)
     */
    private $invoicedTotalPrice;

    /**
     * @var ChargeAct
     *
     * @ORM\ManyToOne(targetEntity="ChargeAct", inversedBy="charges")
     * @ORM\JoinColumn(name="charge_act_id", nullable=true, onDelete="set null")
     */
    private $chargeAct;

    /**
     * @var PayAct
     *
     * @ORM\ManyToOne(targetEntity="PayAct", inversedBy="charges")
     * @ORM\JoinColumn(name="pay_act_id", nullable=true, onDelete="set null")
     */
    private $payAct;

    /**
     * @var string
     *
     * @ORM\Column(name="execution_issues", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $executionIssues;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReservaLog", mappedBy="reserva", cascade={"persist", "remove"})
     */
    private $logs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="control_1", type="string", length=10, nullable=true)
     */
    private $control1;

    /**
     * @var boolean
     *
     * @ORM\Column(name="control_2", type="string", length=10, nullable=true)
     */
    private $control2;

    public function __toString()
    {
        return $this->getSerialNumber();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->passingPlaces = new \Doctrine\Common\Collections\ArrayCollection();
        $this->logs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getSerialNumber()
    {
        return sprintf('T%s-%04s', substr($this->getStartAt()->format('ymd'), 1),
                    substr($this->getId(), -4));
    }

    /**
     * @return integer
     */
    public function countDays()
    {
        if (null === $this->startAt || null === $this->endAt) {
            return 0;
        }

        $diff = date_diff(date_create($this->endAt->format('Y-m-d')), date_create($this->startAt->format('Y-m-d')));

        return $diff->format('%d');
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
     * Set startAt
     *
     * @param \DateTime $startAt
     * @return Reserva
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set providerReference
     *
     * @param string $providerReference
     * @return Reserva
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;

        return $this;
    }

    /**
     * Get providerReference
     *
     * @return string
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * Set serviceDescription
     *
     * @param string $serviceDescription
     * @return Reserva
     */
    public function setServiceDescription($serviceDescription)
    {
        $this->serviceDescription = $serviceDescription;

        return $this;
    }

    /**
     * Get serviceDescription
     *
     * @return string
     */
    public function getServiceDescription()
    {
        return $this->serviceDescription;
    }

    /**
     * Set clientNames
     *
     * @param string $clientNames
     * @return Reserva
     */
    public function setClientNames($clientNames)
    {
        $this->clientNames = $clientNames;

        return $this;
    }

    /**
     * Get clientNames
     *
     * @return string
     */
    public function getClientNames()
    {
        return $this->clientNames;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     * @return Reserva
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set isExecuted
     *
     * @param boolean $isExecuted
     * @return Reserva
     */
    public function setIsExecuted($isExecuted = true)
    {
        $this->isExecuted = $isExecuted;

        return $this;
    }

    /**
     * Get isExecuted
     *
     * @return boolean
     */
    public function getIsExecuted()
    {
        return $this->isExecuted;
    }

    /**
     * Set isCancelled
     *
     * @param boolean $isCancelled
     * @return Reserva
     */
    public function setIsCancelled($isCancelled = true)
    {
        $this->isCancelled = $isCancelled;

        return $this;
    }

    /**
     * Get isCancelled
     *
     * @return boolean
     */
    public function getIsCancelled()
    {
        return $this->isCancelled;
    }

    /**
     * Set driverPayAmount
     *
     * @param string $driverPayAmount
     * @return Reserva
     */
    public function setDriverPayAmount($driverPayAmount)
    {
        $this->driverPayAmount = $driverPayAmount;

        return $this;
    }

    /**
     * Get driverPayAmount
     *
     * @return string
     */
    public function getDriverPayAmount()
    {
        return $this->driverPayAmount;
    }

    /**
     * Set clientPriceAmount
     *
     * @param string $clientPriceAmount
     * @return Reserva
     */
    public function setClientPriceAmount($clientPriceAmount)
    {
        $this->clientPriceAmount = $clientPriceAmount;

        return $this;
    }

    /**
     * Get clientPriceAmount
     *
     * @return string
     */
    public function getClientPriceAmount()
    {
        return $this->clientPriceAmount;
    }

    /**
     * Set startPlace
     *
     * @param string $startPlace
     * @return Reserva
     */
    public function setStartPlace($startPlace)
    {
        $this->startPlace = $startPlace;

        return $this;
    }

    /**
     * Get startPlace
     *
     * @return string
     */
    public function getStartPlace()
    {
        return $this->startPlace;
    }

    /**
     * Set endPlace
     *
     * @param string $endPlace
     * @return Reserva
     */
    public function setEndPlace($endPlace)
    {
        $this->endPlace = $endPlace;

        return $this;
    }

    /**
     * Get endPlace
     *
     * @return string
     */
    public function getEndPlace()
    {
        return $this->endPlace;
    }

    /**
     * Set provider
     *
     * @param \AppBundle\Entity\Provider $provider
     * @return Reserva
     */
    public function setProvider(\AppBundle\Entity\Provider $provider = null)
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
     * @return Reserva
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

    /**
     * Set driver
     *
     * @param \AppBundle\Entity\Driver $driver
     * @return Reserva
     */
    public function setDriver(\AppBundle\Entity\Driver $driver = null)
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
     * Set paidAt
     *
     * @param \DateTime $paidAt
     * @return Reserva
     */
    public function setPaidAt($paidAt)
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * Get paidAt
     *
     * @return \DateTime
     */
    public function getPaidAt()
    {
        return $this->paidAt;
    }

    /**
     * Set cobradoAt
     *
     * @param \DateTime $cobradoAt
     * @return Reserva
     */
    public function setCobradoAt($cobradoAt)
    {
        $this->cobradoAt = $cobradoAt;

        return $this;
    }

    /**
     * Get cobradoAt
     *
     * @return \DateTime
     */
    public function getCobradoAt()
    {
        return $this->cobradoAt;
    }

    /**
     * Add passingPlaces
     *
     * @param \AppBundle\Entity\ReservaPassingPlace $passingPlaces
     * @return Reserva
     */
    public function addPassingPlace(\AppBundle\Entity\ReservaPassingPlace $passingPlaces)
    {
        $this->passingPlaces[] = $passingPlaces;

        $passingPlaces->setReserva($this);

        return $this;
    }

    /**
     * Remove passingPlaces
     *
     * @param \AppBundle\Entity\ReservaPassingPlace $passingPlaces
     */
    public function removePassingPlace(\AppBundle\Entity\ReservaPassingPlace $passingPlaces)
    {
        $this->passingPlaces->removeElement($passingPlaces);
    }

    /**
     * Get passingPlaces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPassingPlaces()
    {
        return $this->passingPlaces;
    }

    /**
     * Set guide
     *
     * @param \AppBundle\Entity\TravelGuide $guide
     * @return Reserva
     */
    public function setGuide(\AppBundle\Entity\TravelGuide $guide = null)
    {
        $this->guide = $guide;

        return $this;
    }

    /**
     * Get guide
     *
     * @return \AppBundle\Entity\TravelGuide
     */
    public function getGuide()
    {
        return $this->guide;
    }

    /**
     * Set isDriverConfirmed
     *
     * @param boolean $isDriverConfirmed
     * @return Reserva
     */
    public function setIsDriverConfirmed($isDriverConfirmed)
    {
        $this->isDriverConfirmed = $isDriverConfirmed;

        return $this;
    }

    /**
     * Get isDriverConfirmed
     *
     * @return boolean
     */
    public function getIsDriverConfirmed()
    {
        return $this->isDriverConfirmed;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     * @return Reserva
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     * @return Reserva
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set enterprise
     *
     * @param \AppBundle\Entity\Enterprise $enterprise
     * @return Reserva
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

    /**
     * Set invoicedAt
     *
     * @param \DateTime $invoicedAt
     * @return Reserva
     */
    public function setInvoicedAt($invoicedAt)
    {
        $this->invoicedAt = $invoicedAt;

        return $this;
    }

    /**
     * Get invoicedAt
     *
     * @return \DateTime
     */
    public function getInvoicedAt()
    {
        return $this->invoicedAt;
    }

    /**
     * Set invoicedTotalPrice
     *
     * @param string $invoicedTotalPrice
     * @return Reserva
     */
    public function setInvoicedTotalPrice($invoicedTotalPrice)
    {
        $this->invoicedTotalPrice = $invoicedTotalPrice;

        return $this;
    }

    /**
     * Get invoicedTotalPrice
     *
     * @return string
     */
    public function getInvoicedTotalPrice()
    {
        return $this->invoicedTotalPrice;
    }

    /**
     * Set invoicedKilometers
     *
     * @param string $invoicedKilometers
     * @return Reserva
     */
    public function setInvoicedKilometers($invoicedKilometers)
    {
        $this->invoicedKilometers = $invoicedKilometers;

        return $this;
    }

    /**
     * Get invoicedKilometers
     *
     * @return string
     */
    public function getInvoicedKilometers()
    {
        return $this->invoicedKilometers;
    }

    /**
     * Set invoicedKilometerPrice
     *
     * @param string $invoicedKilometerPrice
     * @return Reserva
     */
    public function setInvoicedKilometerPrice($invoicedKilometerPrice)
    {
        $this->invoicedKilometerPrice = $invoicedKilometerPrice;

        return $this;
    }

    /**
     * Get invoicedKilometerPrice
     *
     * @return string
     */
    public function getInvoicedKilometerPrice()
    {
        return $this->invoicedKilometerPrice;
    }

    /**
     * Set invoicedHours
     *
     * @param string $invoicedHours
     * @return Reserva
     */
    public function setInvoicedHours($invoicedHours)
    {
        $this->invoicedHours = $invoicedHours;

        return $this;
    }

    /**
     * Get invoicedHours
     *
     * @return string
     */
    public function getInvoicedHours()
    {
        return $this->invoicedHours;
    }

    /**
     * Set invoicedHourPrice
     *
     * @param string $invoicedHourPrice
     * @return Reserva
     */
    public function setInvoicedHourPrice($invoicedHourPrice)
    {
        $this->invoicedHourPrice = $invoicedHourPrice;

        return $this;
    }

    /**
     * Get invoicedHourPrice
     *
     * @return string
     */
    public function getInvoicedHourPrice()
    {
        return $this->invoicedHourPrice;
    }

    /**
     * Set invoicedKilometersPrice
     *
     * @param string $invoicedKilometersPrice
     * @return Reserva
     */
    public function setInvoicedKilometersPrice($invoicedKilometersPrice)
    {
        $this->invoicedKilometersPrice = $invoicedKilometersPrice;

        return $this;
    }

    /**
     * Get invoicedKilometersPrice
     *
     * @return string
     */
    public function getInvoicedKilometersPrice()
    {
        return $this->invoicedKilometersPrice;
    }

    /**
     * Set invoicedHoursPrice
     *
     * @param string $invoicedHoursPrice
     * @return Reserva
     */
    public function setInvoicedHoursPrice($invoicedHoursPrice)
    {
        $this->invoicedHoursPrice = $invoicedHoursPrice;

        return $this;
    }

    /**
     * Get invoicedHoursPrice
     *
     * @return string
     */
    public function getInvoicedHoursPrice()
    {
        return $this->invoicedHoursPrice;
    }

    /**
     * Set invoiceNumber
     *
     * @param string $invoiceNumber
     * @return Reserva
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
     * Set invoiceDriver
     *
     * @param \AppBundle\Entity\Driver $invoiceDriver
     * @return Reserva
     */
    public function setInvoiceDriver(\AppBundle\Entity\Driver $invoiceDriver = null)
    {
        $this->invoiceDriver = $invoiceDriver;

        return $this;
    }

    /**
     * Get invoiceDriver
     *
     * @return \AppBundle\Entity\Driver
     */
    public function getInvoiceDriver()
    {
        return $this->invoiceDriver;
    }

    /**
     * Set pax
     *
     * @param integer $pax
     * @return Reserva
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
     * Set chargeAct
     *
     * @param \AppBundle\Entity\ChargeAct $chargeAct
     * @return Reserva
     */
    public function setChargeAct(\AppBundle\Entity\ChargeAct $chargeAct = null)
    {
        $this->chargeAct = $chargeAct;

        return $this;
    }

    /**
     * Get chargeAct
     *
     * @return \AppBundle\Entity\ChargeAct
     */
    public function getChargeAct()
    {
        return $this->chargeAct;
    }

    /**
     * Set payAct
     *
     * @param \AppBundle\Entity\PayAct $payAct
     * @return Reserva
     */
    public function setPayAct(\AppBundle\Entity\PayAct $payAct = null)
    {
        $this->payAct = $payAct;

        return $this;
    }

    /**
     * Get payAct
     *
     * @return \AppBundle\Entity\PayAct
     */
    public function getPayAct()
    {
        return $this->payAct;
    }

    /**
     * Set executionIssues
     *
     * @param string $executionIssues
     * @return Reserva
     */
    public function setExecutionIssues($executionIssues)
    {
        $this->executionIssues = $executionIssues;

        return $this;
    }

    /**
     * Get executionIssues
     *
     * @return string
     */
    public function getExecutionIssues()
    {
        return $this->executionIssues;
    }

    /**
     * Add log
     *
     * @param \AppBundle\Entity\ReservaLog $log
     * @return Reserva
     */
    public function addLog(\AppBundle\Entity\ReservaLog $log)
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setReserva($this);
        }

        return $this;
    }

    /**
     * Remove log
     *
     * @param \AppBundle\Entity\ReservaLog $log
     */
    public function removeLog(\AppBundle\Entity\ReservaLog $log)
    {
        $this->logs->removeElement($log);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Set control1
     *
     * @param string $control1
     * @return Reserva
     */
    public function setControl1($control1)
    {
        $this->control1 = $control1;

        return $this;
    }

    /**
     * Get control1
     *
     * @return string
     */
    public function getControl1()
    {
        return $this->control1;
    }

    /**
     * Set control2
     *
     * @param string $control2
     * @return Reserva
     */
    public function setControl2($control2)
    {
        $this->control2 = $control2;

        return $this;
    }

    /**
     * Get control2
     *
     * @return string
     */
    public function getControl2()
    {
        return $this->control2;
    }
}
