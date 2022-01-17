<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Driver;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

/**
 * Description of DriverFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class DriverFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('contactInfo', null, array(
                    'required' => false
                ))
                ->add('isDriverGuide', ICheckType::class, array(
                    'label' => 'Es conductor guía (driver guide)',
                    'required' => false
                ))
                ->add('carIndicator', null, array(
                    'label' => 'Indicativo del vehículo',
                    'required' => false
                ))
                ->add('postalAddress', null, array(
                    'required' => false
                ))
                ->add('bankAccount', null, array(
                    'required' => false
                ))
                ->add('nit', null, array(
                    'required' => false
                ))
                ->add('mobilePhone', PhoneNumberType::class, array('required' => false))
                ->add('fixedPhone', PhoneNumberType::class, array('required' => false))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Driver::class
        ));
    }
}
