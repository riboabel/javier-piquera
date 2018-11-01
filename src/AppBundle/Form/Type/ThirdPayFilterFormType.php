<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ReservaTercero;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ThirdPayFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ThirdPayFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceFilterType::class, array(
                'choices' => array(
                    'CLÁSICOS' => ReservaTercero::TYPE_CLASICOS,
                    'MICROBUS' => ReservaTercero::TYPE_MICROBUS,
                    'GUÍA' => ReservaTercero::TYPE_GUIA
                ),
                'choices_as_values' => true,
                'required' => true
            ))
            ->add('paid', ChoiceFilterType::class, array(
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
                        $expression = $filterQuery->getExpr()->isNotNull(sprintf('%s.payAct', $rootAlias));
                    } else {
                        $expression = $filterQuery->getExpr()->isNull(sprintf('%s.payAct', $rootAlias));
                    }

                    return $filterQuery->createCondition($expression);
                },
                'data' => 'no'
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
            'validation_groups' => array('filtering')
        ));
    }
}
