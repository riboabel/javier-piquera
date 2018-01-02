<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ThirdCobro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ThirdCobroActFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdCobroActFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('services', CollectionType::class, array(
            'entry_type' => ThirdCobroServiceType::class
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ThirdCobro::class);
    }
}
