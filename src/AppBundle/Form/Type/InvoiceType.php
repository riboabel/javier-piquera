<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;

/**
 * Description of InvoiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class InvoiceType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('invoicedKilometers')
                ->add('invoicedKilometerPrice')
                ->add('invoicedKilometersPrice')
                ->add('invoicedHours')
                ->add('invoicedHourPrice')
                ->add('invoicedHoursPrice')
                ->add('invoicedTotalPrice')
                ->add('invoiceDriver', null, array(
                    'label' => ' Acreedor',
                    'required' => true,
                    'query_builder' => $this->em->getRepository('AppBundle:Driver')
                        ->createQueryBuilder('d')->orderBy('d.name')
                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Reserva'
        ));
    }

    public function getName()
    {
        return 'invoice';
    }
}
