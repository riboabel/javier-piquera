<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ReservaTercero;
use AppBundle\Entity\ThirdProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ReservaClasicosFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaClasicosFormType extends AbstractType
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
            ->add('serviceType', ServiceTypeSelectorType::class)
            ->add('client')
            ->add('clientSerial')
            ->add('startAt', DateTimeType::class, array(
                'format' => 'dd/MM/yyyy HH:mm',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('endAt', DateTimeType::class, array(
                'format' => 'dd/MM/yyyy HH:mm',
                'widget' => 'single_text',
                'html5' => false,
                'required' => false
            ))
            ->add('startIn', PlaceSelectorType::class)
            ->add('endIn', PlaceSelectorType::class)
            ->add('serviceDescription')
            ->add('clientNames')
            ->add('pax')
            ;

        $qb = $this->manager->getRepository('AppBundle:ThirdProvider')
            ->createQueryBuilder('p')
            ->where('p.type = :type')
            ->orderBy('p.name')
            ->setParameter('type', ThirdProvider::TYPE_CLASICOS)
            ;
        $builder->add('provider', null, array(
            'query_builder' => $qb
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReservaTercero::class);
    }
}
