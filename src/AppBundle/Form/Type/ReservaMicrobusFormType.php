<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ReservaTercero;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ReservaTerceroFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaMicrobusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serviceType', ServiceTypeSelectorType::class)
            ->add('client')
            ->add('clientSerial')
            ->add('provider')
            ->add('providerSerial')
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReservaTercero::class);
    }
}
