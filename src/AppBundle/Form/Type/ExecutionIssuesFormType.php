<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ExecutionIssuesFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ExecutionIssuesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('executionIssues');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
                ->setDefaults(array(
                    'data_class' => 'AppBundle\Entity\Reserva'
                ));
    }
    
    public function getName()
    {
        return 'execution_issues';
    }
}
