<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ServiceType;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ServiceTypeSelectorType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ServiceTypeSelectorType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $qb = $this->manager->getRepository('AppBundle:ServiceType')
            ->createQueryBuilder('s')
            ->orderBy('s.name')
            ;

        $resolver->setDefaults(array(
            'class' => ServiceType::class,
            'query_builder' => $qb
        ));
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
