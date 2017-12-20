<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
/**
 * Description of RolesType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class RolesType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'Administrador' => 'ROLE_ADMIN',
                'Jefe' => 'ROLE_OWNER',
                'Operario' => 'ROLE_USER'
            ),
            'choices_as_values' => true
        ));
    }
}
