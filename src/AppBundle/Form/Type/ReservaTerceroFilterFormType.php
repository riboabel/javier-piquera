<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * ReservaTerceroFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaTerceroFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startAt', DateRangeFilterType::class, array(
                'left_date_options' => array(
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text'
                ),
                'right_date_options' => array(
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text'
                ),
            ))
            ->add('serviceType', TextFilterType::class, array(
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $rootAlias = $filterQuery->getRootAlias();
                    $filterQuery->getQueryBuilder()->join(sprintf('%s.serviceType', $rootAlias), '_st');
                    $expression = $filterQuery->getExpr()->like('_st.name', ':_service_name');

                    return $filterQuery->createCondition($expression, array('_service_name' => array(sprintf('%%%s%%', $values['value']), \PDO::PARAM_STR)));
                }
            ))
            ->add('client', TextFilterType::class, array(
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $rootAlias = $filterQuery->getRootAlias();
                    $filterQuery->getQueryBuilder()->join($rootAlias . '.client', '_client');

                    $expression = $filterQuery->getExpr()->like('_client.name', ':_client_name');
                    $parameters = array('_client_name' => array(
                        sprintf('%%%s%%', $values['value']),
                        \PDO::PARAM_STR
                    ));

                    return $filterQuery->createCondition($expression, $parameters);
                }
            ))
            ->add('clientSerial', TextFilterType::class, array(
                'condition_pattern' => FilterOperands::STRING_CONTAINS
            ))
            ->add('provider', TextFilterType::class, array(
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $filterQuery->getQueryBuilder()->join($field, '_p');

                    $expression = $filterQuery->getExpr()->like('_p.name', ':p_provider_name');
                    $parameters = array('p_provider_name' => array(
                        sprintf('%%%s%%', $values['value']),
                        \PDO::PARAM_STR
                    ));

                    return $filterQuery->createCondition($expression, $parameters);
                }
            ))
            ->add('providerSerial', TextFilterType::class, array(
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $rootAlias = $filterQuery->getRootAlias();

                    $parameters = array();
                    $expression = $filterQuery->getExpr()->orX(
                        $filterQuery->getExpr()->andX(
                            $filterQuery->getExpr()->eq($rootAlias . '.type', ':p_ps_type_microbus'),
                            $filterQuery->getExpr()->like($field, ':p_ps_' . str_replace('.', '_', $field))
                        )
                    );
                    $parameters['p_ps_type_microbus'] = array(
                        ReservaTercero::TYPE_MICROBUS,
                        \PDO::PARAM_STR
                    );
                    $parameters['p_ps_' . str_replace('.', '_', $field)] = array(
                        sprintf('%%%s%%', $values['value']),
                        \PDO::PARAM_STR
                    );


                    if (preg_match('/^CL(?P<year>\d)(?<month>\d{2})(?P<day>\d{2})-(?P<id>\d{4})$/', $values['value'], $parts)) {
                        $startAt = new \DateTime(sprintf('201%s-%s-%s', $parts['year'], $parts['month'], $parts['day']));
                        $id = ltrim($parts['id'], '0');

                        $expression->add(
                            $filterQuery->getExpr()->andX(
                                $filterQuery->getExpr()->eq($rootAlias . '.type', ':p_ps_type_classics'),
                                $filterQuery->getExpr()->andX(
                                    $filterQuery->getExpr()->andX(
                                        $filterQuery->getExpr()->gte($rootAlias . '.startAt', ':p_ps_startAt_left'),
                                        $filterQuery->getExpr()->lte($rootAlias . '.startAt', ':p_ps_startAt_right')
                                    )
                                ),
                                $filterQuery->getExpr()->like($rootAlias . '.id', ':p_ps_id')
                            )
                        );

                        $parameters['p_ps_type_classics'] = array(
                            ReservaTercero::TYPE_CLASICOS,
                            \PDO::PARAM_STR
                        );
                        $parameters['p_ps_startAt_left'] = array(
                            $startAt->format('Y-m-d'),
                            \PDO::PARAM_STR
                        );
                        $parameters['p_ps_startAt_right'] = array(
                            $startAt->format('Y-m-d 23:59:59'),
                            \PDO::PARAM_STR
                        );
                        $parameters['p_ps_id'] = array(
                            sprintf('%%%s', $id),
                            \PDO::PARAM_STR
                        );
                    }

                    return $filterQuery->createCondition($expression, $parameters);
                }
            ))
            ->add('clientNames', TextFilterType::class, array(
                'condition_pattern' => FilterOperands::STRING_CONTAINS
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
