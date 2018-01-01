<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ThirdPayAct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ThirdPayActFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdPayActFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('services', CollectionType::class, array(
            'entry_type' => ThirdPayActServiceType::class
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ThirdPayAct::class,
            'validation_groups' => 'Pay'
        ));
    }
}
