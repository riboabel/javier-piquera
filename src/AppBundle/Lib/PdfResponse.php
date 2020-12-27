<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 7/5/2020
 * Time: 6:04 p.m.
 */

namespace AppBundle\Lib;

use AppBundle\Lib\Reports\ReportInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfResponse extends StreamedResponse
{
    public function __construct(ReportInterface $report, $filename = 'doc.pdf')
    {
        $callback = function() use($report) {
            file_put_contents('php://output', $report->getContent());
        };
        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'application/octet-stream', // 'application/pdf',
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
        ];

        parent::__construct($callback, 200, $headers);
    }
}