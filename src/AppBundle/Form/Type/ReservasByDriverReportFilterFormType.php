<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Driver;
use AppBundle\Entity\ServiceType;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ReservasByDriverReportFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservasByDriverReportFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startAt', DateRangeFilterType::class, array(
                'left_date_options' => array(
                    'label'     => 'Desde',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ),
                'right_date_options' => array(
                    'label'     => 'Hasta',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                )
            ));

        $builder
            ->add('drivers', EntityFilterType::class, array(
                'class' => Driver::class,
                'label' => 'Conductor',
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('d')
                        ->where('d.enabled = true')
                        ->orderBy('d.name');
                },
                'multiple' => true,
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if ($values['value']->count() == 0) {
                        return null;
                    }

                    $rootAlias = $filterQuery->getRootAlias();
                    $filterQuery->getQueryBuilder()->join(sprintf('%s.driver', $rootAlias), '_d');
                    $expression = $filterQuery->getExpr()->in('_d.id', array_map(function(Driver $driver) {
                        return $driver->getId();
                    }, $values['value']->toArray()));

                    return $filterQuery->createCondition($expression);
                }
            ));

        $builder->add('includePlacesAddress', ICheckType::class, array(
            'label' => 'Incluir direcciones',
            'required' => false,
            'apply_filter' => false
        ));

        $builder->add('serviceType', EntityFilterType::class, array(
            'class' => ServiceType::class,
            'label' => 'Servicio',
            'query_builder' => function(EntityRepository $repository) {
                return $repository
                    ->createQueryBuilder('s')
                    ->orderBy('s.name');
            },
            'required' => false,
            'multiple' => true,
            'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                if ($values['value']->count() == 0) {
                    return false;
                }

//                $rootAlias = $filterQuery->getRootAlias();
//                $filterQuery->getQueryBuilder()->join(sprintf('%s.serviceType', $rootAlias), 'st');
                $expression = $filterQuery->getExpr()->in('st.id', array_map(function(ServiceType $serviceType) {
                    return $serviceType->getId();
                }, $values['value']->toArray()));

                return $filterQuery->createCondition($expression);
            }
        ));

        $builder->add('showProviderLogoIfPossible', ICheckType::class, array(
            'label' => 'Mostrar logotipo de agencia',
            'required' => false,
            'data' => true,
            'apply_filter' => false
        ))
        ->add('includeAllRecords', ICheckType::class, array(
            'label' => 'Incluir todos los servicios',
            'required' => false,
            'data' => true,
            'apply_filter' => false
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
