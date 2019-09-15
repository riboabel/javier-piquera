<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ExcelFile extends Constraint
{
    public $message = 'El archivo no es un libre de excel válido o no es posible leer su contenido.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}