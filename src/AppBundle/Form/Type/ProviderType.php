<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Provider;

/**
 * Description of ProviderType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ProviderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('reeupCode', null, array(
                    'required' => false
                ))
                ->add('postalAddress', null, array(
                    'required' => false
                ))
                ->add('contactInfo', null, array(
                    'required' => false
                ))
                ->add('receiveServiceOrder', null, array(
                    'required' => false
                ))
                ->add('receiveInvoice', null, array(
                    'required' => false
                ))
                ->add('contractNumber', null, array(
                    'required' => false
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Provider::class
        ));
    }
}