<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * ICheckType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ICheckType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }

    public function getBlockPrefix()
    {
        return 'icheck';
    }
}
