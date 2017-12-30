<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ThirdProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ThirdProviderFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdProviderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class, array(
                'choices' => array(
                    'Microbus' => 'microbus',
                    'Cläsicos' => 'clasicos'
                ),
                'choices_as_values' => true
            ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ThirdProvider::class);
    }
}
