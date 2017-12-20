<?php

namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Description of ArrayToRoleTransformer
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ArrayToRoleTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (in_array('ROLE_ADMIN', $value)) {
            return 'ROLE_ADMIN';
        } elseif (in_array('ROLE_OWNER', $value)) {
            return 'ROLE_OWNER';
        } else {
            return 'ROLE_USER';
        }
    }

    public function reverseTransform($value)
    {
        if ($value != 'ROLE_USER') {
            return array('ROLE_USER', $value);
        }

        return array('ROLE_USER');
    }
}