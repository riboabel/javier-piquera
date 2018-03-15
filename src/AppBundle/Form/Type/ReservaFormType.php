<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AppBundle\Entity\Reserva;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Description of ReservaFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaFormType extends AbstractType
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
                ->add('startAt', null, array(
                    'format' => 'dd/MM/yyyy HH:mm',
                    'widget' => 'single_text',
                    'html5' => false
                ))
                ->add('endAt', null, array(
                    'format' => 'dd/MM/yyyy HH:mm',
                    'widget' => 'single_text',
                    'html5' => false,
                    'required' => false
                ))
                ->add('provider')
                ->add('providerReference', null, array(
                    'required' => false
                ))
                ->add('serviceType', null, array(
                    'query_builder' => $this->manager->getRepository('AppBundle:ServiceType')
                        ->createQueryBuilder('st')
                        ->orderBy('st.name')
                ))
                ->add('serviceDescription')
                ->add('clientNames', TextType::class)
                ->add('pax')
                ->add('passingPlaces', CollectionType::class, array(
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'entry_type'    => ReservaPassingPlaceType::class
                ))
                ->add('guide', null, array(
                    'label' => 'Guía',
                    'query_builder' => $this->manager->getRepository('AppBundle:TravelGuide')
                        ->createQueryBuilder('g')
                        ->orderBy('g.name')
                ))
                ->add('isDriverConfirmed', ICheckType::class, array(
                    'required' => false,
                    'label' => 'Confirmado'
                ))
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $form->add('startPlace', null, array(
                        'choices' => null !== $data->getId() ? array($data->getStartPlace()->getId() => $data->getStartPlace()) : array(),
                        'required' => true
                    ));

                    $form->add('endPlace', null, array(
                        'choices' => null !== $data->getId() ? array($data->getEndPlace()->getId() => $data->getEndPlace()) : array(),
                        'required' => true
                    ));

                })->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($manager) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $form->add('startPlace', null, array(
                        'choices' => isset($data['startPlace']) && $data['startPlace'] ? array($manager->find('AppBundle:Place', $data['startPlace'])) : null,
                        'required' => true
                    ));

                    $form->add('endPlace', null, array(
                        'choices' => isset($data['endPlace']) && $data['endPlace'] ? array($manager->find('AppBundle:Place', $data['endPlace'])) : null,
                        'required' => true
                    ));
                });

        $qbDrivers = $manager->getRepository('AppBundle:Driver')
                ->createQueryBuilder('d')
                ->orderBy('d.name');
        $qbDrivers->where($qbDrivers->expr()->eq('d.enabled', $qbDrivers->expr()->literal(true)));

        $builder->add('driver', null, array(
            'required' => false,
            'query_builder' => $qbDrivers
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Reserva::class
        ));
    }
}
