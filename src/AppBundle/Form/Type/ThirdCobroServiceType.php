<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ReservaTercero;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ThirdCobroServiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdCobroServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cobroCharge')
            ->add('cobroNotes')
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ReservaTercero::class,
            'validation_groups' => 'Cobro'
        ));
    }
}
