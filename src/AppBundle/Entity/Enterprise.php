<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Enterprise
 *
 * @ORM\Table(name="enterprise")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Enterprise
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
     * @ORM\Column(name="postal_address", type="string", length=254, nullable=true)
     */
    private $postalAddress;

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

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct($name)
    {
        $this->name = $name;
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
     * @return Enterprise
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
     * Set postalAddress
     *
     * @param string $postalAddress
     * @return Enterprise
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
     * Set logoName
     *
     * @param string $logoName
     * @return Enterprise
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Enterprise
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
}
