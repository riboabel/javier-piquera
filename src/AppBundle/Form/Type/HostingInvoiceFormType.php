<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/20/2020
 * Time: 1:47 a.m.
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\HostingInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HostingInvoiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('provider', null, ['label' => 'Proveedor'])
            ->add('lines', CollectionType::class, [
                'entry_type' => HostingInvoiceLineType::class,
                'allow_add' => true,
                'by_reference' => false
            ])
            ->add('grandTotal', null, ['label' => 'Total'])
            ->add('notes', null, ['label' => 'Notas'])
            ->add('currency', ChoiceType::class, [
                'choices' => ['CUP' => 'CUP', 'CUC' => 'CUC', 'USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR'],
                'label' => 'Moneda de pago'
             ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', HostingInvoice::class);
    }
}