<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Provider
 *
 * @ORM\Table(name="provider")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Provider
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
     * @ORM\Column(name="name", type="string", length=254)
     * @Assert\Length(max=254, maxMessage="Este valor no puede superar los 254 caracteres")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_address", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $postalAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="reeup_code", type="string", length=50, nullable=true)
     */
    private $reeupCode;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_info", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $contactInfo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="receive_service_order", type="boolean", options={"default": false})
     */
    private $receiveServiceOrder;

    /**
     * @var boolean
     *
     * @ORM\Column(name="receive_invoice", type="boolean", options={"default": false})
     */
    private $receiveInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default": true})
     */
    private $enabled = true;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_name", type="string", length=100, nullable=true)
     */
    private $logoName;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="logos", fileNameProperty="logoName")
     * @Assert\File(mimeTypes={"image/jpeg", "image/png"})
     */
    private $logoFile;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var Enterprise
     *
     * @ORM\ManyToOne(targetEntity="Enterprise")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $enterprise;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_number", type="string", length=20, nullable=true)
     */
    private $contractNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="last_invoice_auto_increment_value", length=100, nullable=true)
     */
    private $lastInvoiceAutoIncrementValue;

    /**
     * @var string
     *
     * @ORM\Column(name="letters_for_invoice", length=2, nullable=true)
     * @Assert\Length(max=2)
     */
    private $lettersForInvoice;

    public function __construct()
    {
        $this->receiveInvoice = false;
        $this->receiveServiceOrder = false;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param File $file
     * @return Enterprise
     */
    public function setLogoFile(File $file = null)
    {
        $this->logoFile = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return File
     */
    public function getLogoFile()
    {
        return $this->logoFile;
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
     * @return Provider
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
     * Set contactInfo
     *
     * @param string $contactInfo
     * @return Provider
     */
    public function setContactInfo($contactInfo)
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    /**
     * Get contactInfo
     *
     * @return string
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Provider
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set postalAddress
     *
     * @param string $postalAddress
     * @return Provider
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    /**
     * Get postalAddress
     *
     * @return string
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * Set reeupCode
     *
     * @param string $reeupCode
     * @return Provider
     */
    public function setReeupCode($reeupCode)
    {
        $this->reeupCode = $reeupCode;

        return $this;
    }

    /**
     * Get reeupCode
     *
     * @return string
     */
    public function getReeupCode()
    {
        return $this->reeupCode;
    }

    /**
     * Set receiveInvoice
     *
     * @param boolean $receiveInvoice
     * @return Provider
     */
    public function setReceiveInvoice($receiveInvoice)
    {
        $this->receiveInvoice = $receiveInvoice;

        return $this;
    }

    /**
     * Get receiveInvoice
     *
     * @return boolean
     */
    public function getReceiveInvoice()
    {
        return $this->receiveInvoice;
    }

    /**
     * Set logoName
     *
     * @param string $logoName
     * @return Provider
     */
    public function setLogoName($logoName)
    {
        $this->logoName = $logoName;

        return $this;
    }

    /**
     * Get logoName
     *
     * @return string
     */
    public function getLogoName()
    {
        return $this->logoName;
    }

    /**
     * Set enterprise
     *
     * @param \AppBundle\Entity\Enterprise $enterprise
     * @return Provider
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Provider
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set receiveServiceOrder
     *
     * @param boolean $receiveServiceOrder
     * @return Provider
     */
    public function setReceiveServiceOrder($receiveServiceOrder)
    {
        $this->receiveServiceOrder = $receiveServiceOrder;

        return $this;
    }

    /**
     * Get receiveServiceOrder
     *
     * @return boolean
     */
    public function getReceiveServiceOrder()
    {
        return $this->receiveServiceOrder;
    }

    /**
     * Set contractNumber
     *
     * @param string $contractNumber
     * @return Provider
     */
    public function setContractNumber($contractNumber)
    {
        $this->contractNumber = $contractNumber;

        return $this;
    }

    /**
     * Get contractNumber
     *
     * @return string
     */
    public function getContractNumber()
    {
        return $this->contractNumber;
    }

    /**
     * Set lastInvoiceAutoIncrementValue
     *
     * @param string $lastInvoiceAutoIncrementValue
     *
     * @return Provider
     */
    public function setLastInvoiceAutoIncrementValue($lastInvoiceAutoIncrementValue)
    {
        $this->lastInvoiceAutoIncrementValue = $lastInvoiceAutoIncrementValue;

        return $this;
    }

    /**
     * Get lastInvoiceAutoIncrementValue
     *
     * @return string
     */
    public function getLastInvoiceAutoIncrementValue()
    {
        return $this->lastInvoiceAutoIncrementValue;
    }

    /**
     * Set lettersForInvoice
     *
     * @param string $lettersForInvoice
     *
     * @return Provider
     */
    public function setLettersForInvoice($lettersForInvoice)
    {
        $this->lettersForInvoice = $lettersForInvoice;

        return $this;
    }

    /**
     * Get lettersForInvoice
     *
     * @return string
     */
    public function getLettersForInvoice()
    {
        return $this->lettersForInvoice;
    }
}
