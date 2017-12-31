<?php

namespace AppBundle\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ReservaClasicoFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaClasicoFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startAt', DateRangeFilterType::class, array(
            'left_date_options' => array(
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'widget' => 'single_text'
            ),
            'right_date_options' => array(
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'widget' => 'single_text'
            ),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering')
        ));
    }
}
