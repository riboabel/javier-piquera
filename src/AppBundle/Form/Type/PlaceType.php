<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Place;

/**
 * Description of PlaceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PlaceType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('postalAddress')
                ->add('location', null, array(
                    'label' => 'Localidad',
                    'query_builder' => $this->em->getRepository('AppBundle:Location')
                        ->createQueryBuilder('l')->orderBy('l.name')
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Place::class
        ));
    }
}
