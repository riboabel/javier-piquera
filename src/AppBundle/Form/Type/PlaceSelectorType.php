<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Place;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * PlaceSelectorType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PlaceSelectorType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $qb = $this->manager->getRepository('AppBundle:Place')
            ->createQueryBuilder('p')
            ->orderBy('p.name')
            ;

        $resolver->setDefaults(array(
            'class' => Place::class,
            'query_builder' => $qb
        ));
    }
}
