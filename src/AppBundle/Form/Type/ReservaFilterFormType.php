<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use AppBundle\Entity\Provider;
use AppBundle\Entity\ServiceType;
use Doctrine\ORM\EntityManager;

/**
 * Description of ReservaFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaFilterFormType extends AbstractType
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
                ->add('provider', Filters\ChoiceFilterType::class, array(
                    'choices' => $this->getProviderChoices(),
                    'choices_as_values' => true,
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->eq('p.id', ':p_provider');

                        return $filterQuery->createCondition($expression, array('p_provider' => $values['value']));
                    }
                ))
                ->add('serviceType', Filters\ChoiceFilterType::class, array(
                    'choices' => $this->getServiceTypeChoices(),
                    'choices_as_values' => true,
                    'multiple' => true,
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->in('st.id', $values['value']);

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

    /**
     * @return array
     */
    private function getProviderChoices()
    {
        $choices = array();

        $query = $this->manager->createQuery('SELECT p FROM AppBundle:Provider AS p ORDER BY p.name');

        foreach ($query->getResult() as $provider) {
            $choices[$provider->getName()] = $provider->getId();
        }

        return $choices;
    }

    /**
     * @return array
     */
    private function getServiceTypeChoices()
    {
        $choices = array();

        $query = $this->manager->createQuery('SELECT s FROM AppBundle:ServiceType AS s ORDER BY s.name');

        foreach ($query->getResult() as $service) {
            $choices[$service->getName()] = $service->getId();
        }

        return $choices;
    }
}
