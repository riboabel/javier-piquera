<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExcelFileValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$this->checkFilename($value) || !$this->checkFileContents($value)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('file')
                ->addViolation();
        }
    }

    private function checkFilename(UploadedFile $file)
    {
        return in_array(strtolower($file->getClientOriginalExtension()), ['xlsx', 'xls']);
    }

    /**
     * @param UploadedFile $file
     * @return bool
     * @todo
     */
    private function checkFileContents(UploadedFile $file)
    {
        return true;
    }
}