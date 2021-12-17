<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Enterprise;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Description of EnterpriseType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class EnterpriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('postalAddress')
                ->add('logoFile', VichImageType::class, array(
                    'required' => false
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Enterprise::class
        ));
    }
}
