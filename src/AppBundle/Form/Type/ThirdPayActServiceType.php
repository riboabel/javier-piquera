<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ReservaTercero;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ThirdPayActServiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdPayActServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paidCharge')
            ->add('payNotes')
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ReservaTercero::class,
            'validation_groups' => array('Pay')
        ));
    }
}
