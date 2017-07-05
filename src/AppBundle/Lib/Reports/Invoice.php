<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Reserva;

class Invoice extends Report
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Reserva
     */
    private $record;

    /**
     * @var string
     */
    private $logoPath;

    public function __construct(array $params, EntityManager $em)
    {
        parent::__construct('P', 'LETTER');

        $this->em = $em;

        $this->record = $params['record'];
        $this->logoPath = $params['logo_path'];
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
        $this->pdf->Cell(0, 15, 'FACTURA COMERCIAL', 1, 1, 'C');
        
        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Acreedor', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 6, $this->record->getInvoiceDriver() ?: $this->record->getDriver(), 1);
        
        $this->pdf->Cell(40, 6, sprintf('NIT: %s', $this->record->getInvoiceDriver() ? $this->record->getInvoiceDriver()->getNit() : $this->record->getDriver()->getNit()), 1, 1);

        $h = $this->getRowHeight(array(
            array(36, 'Dirección Acreedor'),
            array(120, $this->record->getInvoiceDriver() ? $this->record->getInvoiceDriver()->getPostalAddress() : $this->record->getDriver()->getPostalAddress())
        ));
        $this->pdf->SetFont('', '');
        $this->pdf->MultiCell(35, $h, 'Dirección Acreedor', 1, 'L', false, 0);

        $this->pdf->SetFont('', 'B');
        $this->pdf->MultiCell(120, $h, $this->record->getInvoiceDriver() ? $this->record->getInvoiceDriver()->getPostalAddress() : $this->record->getDriver()->getPostalAddress(), 1, 'L');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, sprintf('Contrato # %s', $this->record->getProvider()->getContractNumber()), 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 6, 'Provincia: Habana', 1);

        $this->pdf->Cell(40, 6, sprintf('Factura # %s', $this->record->getInvoiceNumber()), 1, 1);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(155, 6, sprintf('Orden de trabajo de referencia %s', $this->record->getSerialNumber()), 1, 1);

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Deudor', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 6, $this->record->getProvider()->getName(), 1);

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(40, 6, sprintf('REEUP: %s', $this->record->getProvider()->getReeupCode()), 1, 1);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, 'Dirección Deudor', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->record->getProvider()->getPostalAddress(), 1, 1);

        $this->pdf->ln(7);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, 'Pagar a favor de', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->record->getInvoiceDriver() ?: $this->record->getDriver(), 1, 1);
        
        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, 'Cuenta bancaria', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, sprintf('%s   Banco Metropolitano', $this->record->getInvoiceDriver() ? $this->record->getInvoiceDriver()->getBankAccount() : $this->record->getDriver()->getBankAccount()), 1, 1);

        $this->pdf->ln(25);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(115, 10, 'Descripción del Servicio', 1, 0, 'C');
        $this->pdf->Cell(10, 10, 'UM', 1, 0, 'C');
        $this->pdf->Cell(23, 10, 'Cantidad', 1, 0, 'C');
        $this->pdf->Cell(23, 10, 'Precio CUC', 1, 0, 'C');
        $this->pdf->Cell(0, 10, 'Importe CUC', 1, 1, 'C');

        if (null !== $this->record->getEnterprise()->getLogoName()) {
            $filename = $this->logoPath . DIRECTORY_SEPARATOR . $this->record->getEnterprise()->getLogoName();
            $this->pdf->Image($filename, 165, 27, 40, 25);
        }
        
    }

    private function renderBody()
    {
        $this->pdf->SetFont('', '');
        
        $this->pdf->Cell(115, 10, $this->record->getServiceType()->getName(), 1, 0);
        $this->pdf->Cell(10, 10, 'Km', 'TBR', 0, 'C');
        $this->pdf->Cell(23, 10, sprintf('%0.1f', $this->record->getInvoicedKilometers()), 'TBR', 0, 'C');
        $this->pdf->Cell(23, 10, sprintf('%0.2f', $this->record->getInvoicedKilometerPrice()), 'TBR', 0, 'C');
        $this->pdf->Cell(0, 10, sprintf('%0.2f', $this->record->getInvoicedKilometersPrice()), 'TBR', 1, 'C');

        $this->pdf->Cell(115, 10, 'Horas de espera', 'LBR', 0);
        $this->pdf->Cell(10, 10, 'hora', 'BR', 0, 'C');
        $this->pdf->Cell(23, 10, sprintf('%0.1f', $this->record->getInvoicedHours()), 'BR', 0, 'C');
        $this->pdf->Cell(23, 10, sprintf('%0.2f', $this->record->getInvoicedHourPrice()), 'BR', 0, 'C');
        $this->pdf->Cell(0, 10, sprintf('%0.2f', $this->record->getInvoicedHoursPrice()), 'BR', 1, 'C');

        $this->pdf->Cell(115, 10, '', 'LBR', 0);
        $this->pdf->Cell(10, 10, '', 'BR', 0, 'C');
        $this->pdf->Cell(23, 10, '', 'BR', 0, 'C');
        $this->pdf->Cell(23, 10, '', 'BR', 0, 'C');
        $this->pdf->Cell(0, 10, '', 'BR', 1, 'C');

        $this->pdf->Cell(115, 10, '', 'LBR', 0);
        $this->pdf->Cell(10, 10, '', 'BR', 0, 'C');
        $this->pdf->Cell(23, 10, '', 'BR', 0, 'C');
        $this->pdf->Cell(23, 10, '', 'BR', 0, 'C');
        $this->pdf->Cell(0, 10, '', 'BR', 1, 'C');

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(171, 10, 'Total', 1);
        $this->pdf->Cell(0, 10, sprintf('%0.2f', $this->record->getInvoicedTotalPrice()), 1, 1, 'C');
    }

    private function renderFooter()
    {
        $this->pdf->Ln(10);

        if ($this->record->getServiceDescription()) {
            $this->pdf->Cell(0, 0, 'Descripción del servicio', 'LTR', 1, 'L');
            $this->pdf->SetFont('', '');
            $this->pdf->MultiCell(0, 0, $this->record->getServiceDescription(), 'LBR', 'L');
            $this->pdf->Ln(15);
        } else {
            $this->pdf->Ln(20);
        }

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, '', 1);
        $this->pdf->Cell(80, 6, 'Acreedor', 1, 0, 'C');
        $this->pdf->Cell(0, 6, 'Deudor', 1, 1, 'C');
        
        $this->pdf->Cell(35, 8, 'Nombre', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 8, $this->record->getInvoiceDriver() ?: $this->record->getDriver(), 1, 0, 'C');
        $this->pdf->Cell(0, 8, '', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 8, 'Cargo', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 8, 'Chofer de Taxi Arrendado', 1, 0, 'C');
        $this->pdf->Cell(0, 8, '', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 8, 'Fecha', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 8, $this->record->getStartAt()->format('d/m/Y'), 1, 0, 'C');
        $this->pdf->Cell(0, 8, '', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 9, 'Firma', 1);
        $this->pdf->Cell(80, 9, '', 1, 0, 'C');
        $this->pdf->Cell(0, 9, '', 1, 1, 'C');
    }
}