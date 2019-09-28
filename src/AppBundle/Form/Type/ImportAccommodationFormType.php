<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/14/2019
 * Time: 8:32 PM
 */

namespace AppBundle\Form\Type;

use AppBundle\Validator\Constraints\AccommodationFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportAccommodationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Archivo',
                'constraints' => [
                    new AccommodationFile()
                ]
            ])
            ->add('year', ChoiceType::class, [
                'choices' => $this->getChoicesForYear(),
                'label' => 'Año',
                'required' => false
            ])
            ->add('month', ChoiceType::class, [
                'choices' => $this->getChoicesForMonth(),
                'label' => 'Mes',
                'required' => false
            ])
            ->add('removeBeforeImport', CheckboxType::class, [
                'required' => false,
                'label' => 'Eliminar registros antes de importar'
            ])
            ;
    }

    private function getChoicesForYear()
    {
        $years = range(date('Y'), date('Y') + 3);

        return array_combine($years, $years);
    }

    private function getChoicesForMonth()
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
    }
}