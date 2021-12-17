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
class ProviderFormType extends AbstractType
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
                'label' => "Recibe órdenes de trabajo",
                'required' => false
            ))
            ->add('receiveInvoice', null, array(
                'label' => "Recibe facturas",
                'required' => false
            ))
            ->add('contractNumber', null, array(
                'required' => false
            ))
            ->add('lastInvoiceAutoIncrementValue');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Provider::class
        ));
    }
}
