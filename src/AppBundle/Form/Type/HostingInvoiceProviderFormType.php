<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/20/2020
 * Time: 12:36 a.m.
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\HostingInvoiceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HostingInvoiceProviderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['label' => 'Nombre'])
            ->add('prefix', null, ['label' => 'Prefijo de factura'])
            ->add('region', null, ['label' => 'Región']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', HostingInvoiceProvider::class);
    }
}