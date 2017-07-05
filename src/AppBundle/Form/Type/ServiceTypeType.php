<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\ServiceType;

/**
 * Description of ServiceTypeType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ServiceTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('isMultiple', null, array(
                    'required' => false
                ))
                ->add('defaultPrice')
                ->add('defaultPayAmount')
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ServiceType::class
        ));
    }
}
