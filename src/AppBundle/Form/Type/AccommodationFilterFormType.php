<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/15/2019
 * Time: 6:30 PM
 */

namespace AppBundle\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateRangeFilterType::class, [
                'label' => 'Inicio',
                'left_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Desde'
                ],
                'right_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Hasta'
                ]
            ])
            ->add('endDate', DateRangeFilterType::class, [
                'label' => 'Terminación',
                'left_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Hasta'
                ],
                'right_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Hasta'
                ]
            ])
            ->add('nights', NumberRangeFilterType::class, [
                'label' => 'Noches',
                'left_number_options' => [
                    'label' => 'Desde',
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL
                ],
                'right_number_options' => [
                    'label' => 'Hasta',
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL
                ]
            ])
            ->add('reference', TextFilterType::class, [
                'label' => 'Referencia',
                'condition_pattern' => FilterOperands::STRING_CONTAINS
            ])
            ->add('leadClient', TextFilterType::class, [
                'label' => 'Cliente',
                'condition_pattern' => FilterOperands::STRING_CONTAINS
            ])
            ->add('pax', NumberRangeFilterType::class, [
                'label' => 'Pax',
                'left_number_options' => [
                    'label' => 'Desde',
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL
                ],
                'right_number_options' => [
                    'label' => 'Hasta',
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL
                ]
            ])
            ->add('fromLocation', TextFilterType::class, [
                'label' => 'Casa',
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $expression = $filterQuery->getExpr()->like('p.name', ':q_fromLocation');

                    return $filterQuery->createCondition($expression, ['q_fromLocation' => sprintf('%%%s%%', $values['value'])]);
                }
            ])
            ->add('fromRegion', TextFilterType::class, [
                'label' => 'Región',
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $expression = $filterQuery->getExpr()->like('r.name', ':q_fromRegion');

                    return $filterQuery->createCondition($expression, ['q_fromRegion' => sprintf('%%%s%%', $values['value'])]);
                }
            ])
            ->add('cost', NumberRangeFilterType::class, [
                'label' => 'Costo',
                'left_number_options' => [
                    'label' => 'Desde',
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL
                ],
                'right_number_options' => [
                    'label' => 'Hasta',
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL
                ]
            ])
            ->add('paidAt', BooleanFilterType::class, [
                'label' => 'Pagado',
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $expression = $values['value'] == 'y' ? $filterQuery->getExpr()->isNotNull($field) :
                        $filterQuery->getExpr()->isNull($field);

                    return $filterQuery->createCondition($expression);
                }
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['filtering']
        ]);
    }
}