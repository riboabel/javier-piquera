<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ProfileType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ProfileType extends AbstractType
{
    public function getName()
    {
        return 'user_profile';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullName', null, array(
                    'label'     => 'Nombre completo',
                    'required'  => true
                ))
                ->add('plainPassword', 'repeated', array(
                    'first_options' => array(
                        'label' => 'Contraseña'
                    ),
                    'second_options' => array(
                        'label' => 'Repetir contraseña'
                    ),
                    'required' => false,
                    'type' => 'password'
                ))
                ->add('imageFile', 'vich_image', array(
                    'required' => false
                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }
}
