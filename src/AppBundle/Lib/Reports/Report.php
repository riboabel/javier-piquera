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


    protected function getSizesForImage($imagePath, $maxHeight, $maxWidth)
    {
        $info = getimagesize($imagePath);

        if ($info[0] <= $maxWidth && $info[1] <= $maxHeight) {
            return array(
                'w' => $info[0],
                'h' => $info[1]
            );
        } else {
            $f = $info[0] / $info[1];

            if ($maxWidth > $info[0]) {
                return array(
                    'w' => $maxWidth,
                    'h' => round($maxWidth / $f)
                );
            } else {
                return array(
                    'w' => $f * $maxHeight,
                    'h' => $maxHeight
                );
            }
        }

        return array(
            'w' => 0,
            'h' => 0
        );
    }


    protected function getPdfContent()
    {
        ob_start();

        $this->pdf->Output();

        $content = ob_get_clean();

        return $content;
    }
}