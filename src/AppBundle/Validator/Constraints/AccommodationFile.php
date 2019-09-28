<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/27/2019
 * Time: 9:53 AM
 */

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AccommodationFile extends Constraint
{
    public $messageNoValidFile = 'El fichero no es un ecel válido';
    public $messageNoRequiredColumns = 'Las columnas necesarias para la importación no fueron encontradas [%columns%]';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'accommodation_file';
    }
}