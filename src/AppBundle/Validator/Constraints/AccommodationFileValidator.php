<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/27/2019
 * Time: 9:58 AM
 */

namespace AppBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AccommodationFileValidator extends ConstraintValidator
{
    const COLUMNS = ['ServiceName', 'StartDate', 'EndDate', 'Nights', 'OurReference', 'LeadClient', 'Pax', 'PrimaryLocation', 'Cost'];

    private $factory;

    public function __construct(\Liuggio\ExcelBundle\Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->fileIsValid($value)) {
            $this->context->addViolation($constraint->messageNoValidFile);
        } elseif ($missedColumns = $this->fileHasColumns($value)) {
            $this->context->addViolation($constraint->messageNoRequiredColumns, ['%columns%' => implode(', ', $missedColumns)]);
        }
    }

    private function fileIsValid(UploadedFile $file)
    {
        $error = false;
        try {
            $this->factory->createPHPExcelObject($file->getPathname());
        } catch (\Exception $e) {
            $error = true;
        }

        return !$error;
    }

    private function fileHasColumns($file)
    {
        $haveColumns = [];

        $book = $this->factory->createPHPExcelObject($file->getPathname());

        $book->setActiveSheetIndex(0);
        $sheet = $book->getActiveSheet();
        $lastColumn = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn(1));
        for ($i = 0; $i < $lastColumn; $i++) {
            $cell = $sheet->getCellByColumnAndRow($i, 1);
            if ($cell->getValue() && in_array($cell->getValue(), self::COLUMNS)) {
                $haveColumns[$cell->getValue()] = true;
            }
        }

        return array_diff(self::COLUMNS, array_keys($haveColumns));
    }
}