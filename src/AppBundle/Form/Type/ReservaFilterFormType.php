<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * Description of ReservaFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaFilterFormType extends AbstractType
{
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
                ->add('isExecuted', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'Sí' => 'yes',
                        'No' => 'no'
                    ),
                    'choices_as_values' => true,
                    'apply_filter' => array($this, 'choiceApplyFilter')
                ))
                ->add('isCancelled', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'Sí' => 'yes',
                        'No' => 'no'
                    ),
                    'choices_as_values' => true,
                    'apply_filter' => array($this, 'choiceApplyFilter')
                ))
                ->add('isDriverConfirmed', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'Sí' => 'yes',
                        'No' => 'no'
                    ),
                    'choices_as_values' => true,
                    'apply_filter' => array($this, 'choiceApplyFilter')
                ))
                ->add('isDriverAssigned', ReservaAssignedFilterType::class, array(
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value']['choice'])) {
                            return null;
                        }

                        if ($values['value']['choice'] === 'with-drivers') {
                            if (!empty($values['value']['drivers'])) {
                                $expression = $filterQuery->getExpr()->orX(
                                    $filterQuery->getExpr()->isNull(sprintf('%s.driver', $filterQuery->getRootAlias())),
                                    $filterQuery->getExpr()->in('d.id', $values['value']['drivers'])
                                );
                            } else {
                                $expression = $filterQuery->getExpr()->isNull(sprintf('%s.driver', $filterQuery->getRootAlias()));
                            }
                        } elseif ($values['value']['choice'] === 'no') {
                            $expression = $filterQuery->getExpr()->isNull(sprintf('%s.driver', $filterQuery->getRootAlias()));
                        } else {
                            $expression = $filterQuery->getExpr()->isNotNull(sprintf('%s.driver', $filterQuery->getRootAlias()));
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

    public function choiceApplyFilter(QueryInterface $filterQuery, $field, $values)
    {
        if (empty($values['value'])) {
            return null;
        }

        $expression = $filterQuery->getExpr()->eq($field, $filterQuery->getExpr()->literal($values['value'] == 'yes'));

        return $filterQuery->createCondition($expression);
    }
}
