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
                ->add('clientNames', Filters\TextFilterType::class, array(
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->andX();
                        $params = array();

                        foreach (explode(' ', $values['value']) as $index => $word) {
                            if ($word) {
                                $expression->add($filterQuery->getExpr()->like('r.clientNames', sprintf(':r_clientNames_%s', $index)));
                                $params[sprintf(':r_clientNames_%s', $index)] = sprintf('%%%s%%', $word);
                            }
                        }

                        return $filterQuery->createCondition($expression, $params);
                    }
                ))
                ->add('serviceTypeName', Filters\TextFilterType::class, array(
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $rootAlias = $filterQuery->getRootAlias();
                        $joinParts = $filterQuery->getQueryBuilder()->getDQLPart('join');
                        $alias = '';
                        foreach ($joinParts as $joins) {
                            foreach ($joins as $join) {
                                if ($join->getJoin() === sprintf('%s.serviceType', $rootAlias)) {
                                    $alias = $join->getAlias();
                                }
                            }
                        }
                        if (!$alias) {
                            $alias = 'st';
                            $filterQuery->getQueryBuilder()->join(sprintf('%s.serviceType', $rootAlias), $alias);
                        }

                        $expression = $filterQuery->getExpr()->andX();
                        $params = array();

                        foreach (explode(' ', $values['value']) as $index => $word) {
                            if ($word) {
                                $expression->add($filterQuery->getExpr()->like(sprintf('%s.name', $alias), sprintf(':r_serviceType_name_%s', $index)));
                                $params[sprintf(':r_serviceType_name_%s', $index)] = sprintf('%%%s%%', $word);
                            }
                        }

                        return $filterQuery->createCondition($expression, $params);
                    }
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering')
        ));
    }
}
