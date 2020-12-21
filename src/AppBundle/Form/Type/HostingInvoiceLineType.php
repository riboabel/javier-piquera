<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/20/2020
 * Time: 1:47 a.m.
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\HostingInvoiceLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HostingInvoiceLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookingReference', null, ['label' => 'Referencia'])
            ->add('service', null, ['label' => 'Servicio'])
            ->add('clientName', null, ['label' => 'Cliente'])
            ->add('startDate', null, [
                'label' => 'Inicio',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy'
            ])
            ->add('endDate', null, [
                'label' => 'Terminación',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy'
            ])
            ->add('nights', null, ['label' => 'Noches'])
            ->add('rowTotal', null, ['label' => 'Costo'])
            ->add('accommodationId', HiddenType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', HostingInvoiceLine::class);
    }
}