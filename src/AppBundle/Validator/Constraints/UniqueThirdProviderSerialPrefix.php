<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * UniqueThirdProviderSerialPrefix
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Annotation
 */
class UniqueThirdProviderSerialPrefix extends Constraint
{
    public $message = 'Este valor ya está siendo usado por otro proveedor';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'unique_third_provider_serial_prefix';
    }
}
