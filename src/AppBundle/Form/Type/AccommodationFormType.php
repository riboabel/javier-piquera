<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 10/19/2019
 * Time: 1:03 p.m.
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\HAccommodation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', null, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'html5' => false,
                'label' => 'Inicio'
            ])
            ->add('endDate', null, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'html5' => false,
                'label' => 'Terminación'
            ])
            ->add('nights', null, [
                'label' => 'Noches'
            ])
            ->add('reference', null, [
                'label' => 'Referencia'
            ])
            ->add('leadClient', null, [
                'label' => 'Cliente'
            ])
            ->add('pax', null, [
                'label' => 'PAX'
            ])
            ->add('provider', null, [
                'label' => 'Lugar'
            ])
            ->add('cost', null, [
                'label' => 'Costo'
            ])
            ->add('details', null, [
                'label' => 'Detalles'
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', HAccommodation::class);
    }
}