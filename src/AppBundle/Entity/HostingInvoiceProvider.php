<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/19/2020
 * Time: 11:55 p.m.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class HostingInvoiceProvider
 *
 * @ORM\Entity
 * @ORM\Table(name="hosting_invoice_provider")
 * @UniqueEntity(fields={"prefix"})
 */
class HostingInvoiceProvider
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
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @var HRegion
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\HRegion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     * @Assert\NotBlank
     * @Assert\Length(max="2")
     */
    private $prefix;

    /**
     * @var integer
     *
     * @ORM\Column(name="next_autoincrement", type="integer")
     */
    private $nextAutoincrement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_autoincrement_reset_at", type="datetime", nullable=true)
     */
    private $lastAutoincrementResetAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    public function __toString()
    {
        return $this->getName();
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
     *
     * @return HostingInvoiceProvider
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
     * Set prefix
     *
     * @param string $prefix
     *
     * @return HostingInvoiceProvider
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set nextAutoincrement
     *
     * @param integer $nextAutoincrement
     *
     * @return HostingInvoiceProvider
     */
    public function setNextAutoincrement($nextAutoincrement)
    {
        $this->nextAutoincrement = $nextAutoincrement;

        return $this;
    }

    /**
     * Get nextAutoincrement
     *
     * @return integer
     */
    public function getNextAutoincrement()
    {
        return $this->nextAutoincrement;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return HostingInvoiceProvider
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
     * Set region
     *
     * @param \AppBundle\Entity\HRegion $region
     *
     * @return HostingInvoiceProvider
     */
    public function setRegion(\AppBundle\Entity\HRegion $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \AppBundle\Entity\HRegion
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set lastAutoincrementResetAt
     *
     * @param \DateTime $lastAutoincrementResetAt
     *
     * @return HostingInvoiceProvider
     */
    public function setLastAutoincrementResetAt($lastAutoincrementResetAt)
    {
        $this->lastAutoincrementResetAt = $lastAutoincrementResetAt;

        return $this;
    }

    /**
     * Get lastAutoincrementResetAt
     *
     * @return \DateTime
     */
    public function getLastAutoincrementResetAt()
    {
        return $this->lastAutoincrementResetAt;
    }
}
