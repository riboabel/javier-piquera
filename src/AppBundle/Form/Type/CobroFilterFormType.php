<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use AppBundle\Entity\Provider;
use Doctrine\ORM\EntityManager;

/**
 * Description of CobroFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class CobroFilterFormType extends AbstractType
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
                ->add('provider', Filters\EntityFilterType::class, array(
                    'class' => Provider::class,
                    'query_builder' => $this->manager->getRepository('AppBundle:Provider')
                        ->createQueryBuilder('p')
                        ->orderBy('p.name')
                ))
                ->add('cobradoAt', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'No cobrado' => 'no-cobrado',
                        'Cobrado' => 'cobrado'
                    ),
                    'choices_as_values' => true,
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        if ($values['value'] === 'cobrado') {
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
