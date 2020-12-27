<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/20/2020
 * Time: 2:32 p.m.
 */

namespace AppBundle\Lib\Reports;

class HostingInvoice extends Report
{
    private $invoice;

    public function __construct(\AppBundle\Entity\HostingInvoice $invoice)
    {
        parent::__construct('L', 'LETTER');

        $this->invoice = $invoice;
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFont('Helvetica', 'B', 15);
        $this->pdf->Cell(220, 15, 'FACTURA DE SERVICIOS / INVOICE', 1, 0, 'C');
        $this->pdf->Cell(0, 15, 'No. ' . $this->invoice->getInvoiceNumber(), 1, 1, 'C');

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Acreedor', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->invoice->getProvider()->getName(), 1, 1);

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Deudor', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, 'CUBADIRECT TRAVEL', 1, 1);

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Fecha', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->invoice->getCreatedAt()->format('d/m/Y'), 1, 1);

        $this->pdf->ln(4);
    }

    private function renderBody()
    {
        $this->pdf->SetFont('', 'B', 8);
        $this->pdf->Cell(30, 9, 'REFERENCIA', 1, 0, 'C');
        $this->pdf->Cell(70, 9, 'SERVICIO', 1, 0, 'C');
        $this->pdf->Cell(70, 9, 'CLIENTE', 1, 0, 'C');
        $this->pdf->Cell(25, 9, 'DESDE', 1, 0, 'C');
        $this->pdf->Cell(25, 9, 'HASTA', 1, 0, 'C');
        $this->pdf->Cell(15, 9, 'NOCHES', 1, 0, 'C');
        $this->pdf->Cell(0, 9, 'IMPORTE', 1, 1, 'C');

        $this->pdf->SetFont('', '', 8);
        foreach ($this->invoice->getLines() as $line) {
            $columns = array(
                [30, $line->getBookingReference()],
                [70, $line->getService()],
                [70, $line->getClientName()],
                [25, $line->getStartDate()->format('d/m/Y')],
                [25, $line->getEndDate()->format('d/m/Y')],
                [15, $line->getNights()],
                [0, sprintf('%0.2f', $line->getRowTotal())]
            );
            $h = $this->getRowHeight($columns);

            $this->pdf->MultiCell(30, $h, $line->getBookingReference(), 1, 'L', false, 0);
            $this->pdf->MultiCell(70, $h, $line->getService(), 1, 'L', false, 0);
            $this->pdf->MultiCell(70, $h, $line->getClientName(), 1, 'L', false, 0);
            $this->pdf->MultiCell(25, $h, $line->getStartDate()->format('d/m/Y'), 1, 'C', false, 0);
            $this->pdf->MultiCell(25, $h, $line->getEndDate()->format('d/m/Y'), 1, 'C', false, 0);
            $this->pdf->MultiCell(15, $h, $line->getNights(), 1, 'C', false, 0);
            $this->pdf->MultiCell(0, $h, sprintf('%0.2f', $line->getRowTotal()), 1, 'R');
        }

        $this->pdf->SetY(160);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(15, 10, 'TOTAL', 0, 0, 'R');
        $this->pdf->Cell(0, 10, sprintf('%0.2f', $this->invoice->getGrandTotal()), 0, 1, 'R');
    }

    private function renderFooter()
    {
        $this->pdf->SetY(160);
        $this->pdf->SetFont('', 'B');
    }
}