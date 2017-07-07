<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\InvoiceLine;
use AppBundle\Entity\Reserva;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\CallbackTransformer;

/**
 * InvoiceLineType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class InvoiceLineType extends AbstractType
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
        $builder
                ->add('service', ChoiceType::class, array(
                    'required' => true,
                    'mapped' => false,
                    'choices_as_values' => true
                ))
                ->add('meassurementUnit')
                ->add('quantity')
                ->add('unitPrice')
                ->add('totalPrice')
                ->add('notes')
                ->add('serviceName', HiddenType::class)
                ->add('clientsName', HiddenType::class)
                ->add('clientReference', HiddenType::class)
                ->add('serviceSerialNumber', HiddenType::class)
                ;

        $manager = $this->manager;
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($manager) {
            $form = $event->getForm();
            $data = $event->getData();

            $qb = $manager->getRepository('AppBundle:Reserva')
                    ->createQueryBuilder('r')
                    ;
            $andX = $qb->expr()->andX();

            if ($data['service']) {
                $andX->add($qb->expr()->eq('r.id', ':reserva'));
                $qb->setParameter('reserva', $data['service']);
                $qb->where($andX);
            }

            $form
                    ->remove('service')
                    ->add('service', EntityType::class, array(
                        'mapped' => false,
                        'class' => Reserva::class,
                        'query_builder' => $qb
                    ))
                    ;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', InvoiceLine::class);
    }
}
