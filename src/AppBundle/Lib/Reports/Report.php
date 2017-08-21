<?php

namespace AppBundle\Lib\Reports;

abstract class Report implements ReportInterface
{
    /**
     * @var CleanPdf
     */
    protected $pdf;

    public function __construct($orintation = 'P', $format = 'A4')
    {
        $this->pdf = new CleanPdf($orintation, 'mm', $format);
    }

    protected function getRowHeight(array $columns)
    {
        $this->pdf->startTransaction();
        $this->pdf->addPage();

        $maxH = 0;

        foreach ($columns as $i => $content) {
            $this->pdf->MultiCell($content[0], 0, $content[1], 1, 'J', false, 0);

            if ($maxH < $this->pdf->getLastH()) {
                $maxH = $this->pdf->getLastH();
            }
        }

        $this->pdf->rollbackTransaction(true);

        return $maxH;
    }

    protected function getPdfContent()
    {
        ob_start();

        $this->pdf->Output();

        $content = ob_get_clean();

        return $content;
    }
}