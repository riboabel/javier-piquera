<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints\UniqueThirdProviderSerialPrefix;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ThirdProvider
 *
 * @ORM\Entity
 * @ORM\Table(name="third_provider")
 * @UniqueThirdProviderSerialPrefix
 */
class ThirdProvider
{
    const TYPE_MICROBUS = 'microbus';
    const TYPE_CLASICOS = 'clasicos';

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     * @Assert\Regex("/^(clasicos|microbus)$/")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_serial_generator", type="boolean", options={"default": false})
     */
    private $isSerialGenerator;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_prefix", length=2, nullable=true)
     * @Assert\Length(min="2", max="2")
     * @Assert\Regex("/^[A-Z]{2}$/", message="Solo se permiten letras masyúsculas")
     */
    private $serialPrefix;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
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
     * @return ThirdProvider
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
     * Set type
     *
     * @param string $type
     *
     * @return ThirdProvider
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set isSerialGenerator
     *
     * @param boolean $isSerialGenerator
     *
     * @return ThirdProvider
     */
    public function setIsSerialGenerator($isSerialGenerator)
    {
        $this->isSerialGenerator = $isSerialGenerator;

        return $this;
    }

    /**
     * Get isSerialGenerator
     *
     * @return boolean
     */
    public function getIsSerialGenerator()
    {
        return $this->isSerialGenerator;
    }

    /**
     * Set serialPrefix
     *
     * @param string $serialPrefix
     *
     * @return ThirdProvider
     */
    public function setSerialPrefix($serialPrefix)
    {
        $this->serialPrefix = $serialPrefix;

        return $this;
    }

    /**
     * Get serialPrefix
     *
     * @return string
     */
    public function getSerialPrefix()
    {
        return $this->serialPrefix;
    }
}
