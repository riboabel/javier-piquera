<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Place;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

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
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('postalAddress')
                ->add('location', null, array(
                    'label' => 'Localidad',
                    'query_builder' => $this->manager->getRepository('AppBundle:Location')
                        ->createQueryBuilder('l')
                        ->orderBy('l.name')
                ))
                ->add('mobilePhone', PhoneNumberType::class, array('required' => false))
                ->add('fixedPhone', PhoneNumberType::class, array('required' => false))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Place::class
        ));
    }
}
