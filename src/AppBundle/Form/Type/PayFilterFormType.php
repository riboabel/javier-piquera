<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use AppBundle\Entity\Driver;
use Doctrine\ORM\EntityManager;

/**
 * Description of PayFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PayFilterFormType extends AbstractType
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
                ->add('startAt', Filters\DateRangeFilterType::class, array(
                    'left_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'widget' => 'single_text'
                    ),
                    'right_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'widget' => 'single_text'
                    )
                ))
                ->add('driver', Filters\EntityFilterType::class, array(
                    'class' => Driver::class,
                    'query_builder' => $this->manager->getRepository('AppBundle:Driver')
                        ->createQueryBuilder('d')
                        ->orderBy('d.name')
                ))
                ->add('paidAt', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'No pagado' => 'no-pagado',
                        'Pagado' => 'pagado'
                    ),
                    'choices_as_values' => true,
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        if ($values['value'] === 'pagado') {
                            $expression = $filterQuery->getExpr()->isNotNull($field);
                        } else {
                            $expression = $filterQuery->getExpr()->isNull($field);
                        }

                        return $filterQuery->createCondition($expression);
                    }
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering')
        ));
    }
}
