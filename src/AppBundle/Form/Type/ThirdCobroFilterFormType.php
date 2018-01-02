<?php

namespace AppBundle\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;

/**
 * ThirdCobroFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdCobroFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cobrado', ChoiceFilterType::class, array(
                'choices' => array(
                    'Sí' => 'yes',
                    'No' => 'no'
                ),
                'choices_as_values' => true,
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $rootAlias = $filterQuery->getRootAlias();

                    if ('yes' === $values['value']) {
                        $expression = $filterQuery->getExpr()->isNotNull(sprintf('%s.cobro', $rootAlias));
                    } else {
                        $expression = $filterQuery->getExpr()->isNull(sprintf('%s.cobro', $rootAlias));
                    }

                    return $filterQuery->createCondition($expression);
                }
            ))
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
                )
            ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => 'filtering'
        ));
    }
}
