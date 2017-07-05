<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\ReservaPassingPlace;

class ReservaPassingPlaceType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $manager = $this->manager;

        $builder
                ->add('stayAt', null, array(
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy'
                ))
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $form->add('place', null, array(
                        'choices' => null !== $data && null !== $data->getId() ? array($data->getPlace()->getId() => $data->getPlace()) : array(),
                        'required' => true
                    ));

                })->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($manager) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $form->add('place', null, array(
                        'choices' => isset($data['place']) && $data['place'] ? $manager->find('AppBundle:Place', $data['place']) : null,
                        'required' => true
                    ));
                });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ReservaPassingPlace::class
        ));
    }
}
