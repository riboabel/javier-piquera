<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Table(name="fos_user")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Ingrese su nombre completo", groups={"Profile"})
     * @Assert\Length(min=3, max=255, minMessage="Nombre demasiado corto", maxMessage="Nombre demasiado largo", groups={"Profile"})
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="image_name", type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="profile_image", fileNameProperty="imageName")
     * @Assert\File(
     *      mimeTypes={"image/jpg", "image/jpeg", "image/png"},
     *      mimeTypesMessage="El tipo de archiv no es válido ({{ type }}). Tipos permitidos: {{ types }}."
     * )
     */
    private $imageFile;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Enterprise")
     * @ORM\JoinTable(name="fos_user_enterprise")
     */
    private $enterprises;

    public function __construct()
    {
        parent::__construct();

        $this->email = uniqid() . '@application.com';
    }

    public function __toString()
    {
        return $this->getFullName() ?: $this->getUsername();
    }

    /**
     * @param File $imageFile
     * @return User
     */
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     * @return User
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string 
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return User
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
     * Add enterprises
     *
     * @param \AppBundle\Entity\Enterprise $enterprises
     * @return User
     */
    public function addEnterprise(\AppBundle\Entity\Enterprise $enterprises)
    {
        $this->enterprises[] = $enterprises;

        return $this;
    }

    /**
     * Remove enterprises
     *
     * @param \AppBundle\Entity\Enterprise $enterprises
     */
    public function removeEnterprise(\AppBundle\Entity\Enterprise $enterprises)
    {
        $this->enterprises->removeElement($enterprises);
    }

    /**
     * Get enterprises
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEnterprises()
    {
        return $this->enterprises;
    }
}
