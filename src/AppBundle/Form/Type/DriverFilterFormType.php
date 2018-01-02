<?php

namespace AppBundle\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DriverFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class DriverFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isDriverGuide', ChoiceFilterType::class, array(
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

                    $expression = $filterQuery->getExpr()->eq(sprintf('%s.isDriverGuide', $rootAlias), ':isDriverGuide');

                    return $filterQuery->createCondition($expression, array('isDriverGuide' => $values['value'] === 'yes'));
                }
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
