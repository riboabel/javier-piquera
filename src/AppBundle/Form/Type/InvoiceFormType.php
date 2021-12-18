<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Entity\Invoice;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManager;

/**
 * InvoiceFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class InvoiceFormType extends AbstractType
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
        $qb = $this->manager->getRepository('AppBundle:Provider')
                ->createQueryBuilder('p')
                ->orderBy('p.name')
                ;
        $qb->where($qb->expr()->eq('p.receiveInvoice', 'true'));

        $builder
            ->add('provider', null, array(
                'query_builder' => $qb,
                'choice_label' => function(Provider $p) {
                    return sprintf('%s (%s)', $p, $p->getLettersForInvoice() ?: 'sin letra para número de factura');
                },
                'choice_attr' => function(Provider $p) {
                    $attr = [];
                    if (!$p->getLettersForInvoice()) {
                        $attr['data-has-no-letter'] = 'true';
                    }

                    return $attr;
                }
            ))
            ->add('driver')
            ->add('modelName', ChoiceType::class, array(
                'choices' => array(
                    'ATRIO' => 'ATRIO',
                    'GENERAL' => 'GENERAL'
                ),
                'choices_as_values' => true
            ))
            ->add('lines', CollectionType::class, array(
                'entry_type' => InvoiceLineType::class,
                'allow_add' => true,
                'by_reference' => false
            ))
            ->add('notes', TextareaType::class, array(
                'required' => false
            ))
            ->add('totalCharge');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Invoice::class);
    }
}
