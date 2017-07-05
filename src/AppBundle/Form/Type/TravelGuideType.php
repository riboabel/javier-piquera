<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\TravelGuide;

/**
 * Description of TravelGuideType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class TravelGuideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('contactInfo')
                ->add('providers', null, array(
                    'multiple' => true
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => TravelGuide::class
        ));
    }
}
