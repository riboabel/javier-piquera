<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Reserva;

class EmptyJobOrder extends Report
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
        $this->pdf->SetLeftMargin(30);
        $this->pdf->SetRightMargin(30);

        $this->pdf->addPage();

        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFontSize(16);
        if (null !== $this->record->getEnterprise()->getLogoName()) {
            $logo = $this->logoPath . DIRECTORY_SEPARATOR . $this->record->getEnterprise()->getLogoName();
            $this->pdf->Image($logo, '', '', 0, 9);
        }
        $this->pdf->Cell(0, 10, sprintf('ORDEN DE TRABAJO # %s', $this->record->getSerialNumber()), 1, 1, 'C');
    }

    private function renderBody()
    {
        $this->pdf->SetFontSize(12);
        $this->pdf->Cell(42, 0, 'TAXI ARRENDADO:', 'LBT', 0, 'L');
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(20, 0, $this->record->getDriver()->getCarIndicator(), 'TRB');
        $this->pdf->setFont('', '');
        $this->pdf->Cell(20, 0, '', 1);
        $this->pdf->Cell(30, 0, sprintf('Contrato: %s', $this->record->getProvider()->getContractNumber()), 1);
        $this->pdf->Cell(0, 0, '', 1, 1);

        $this->pdf->Cell(24, 0, 'Conductor: ', 'TLB', 0, 'R');
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 0, $this->record->getDriver()->getName(), 'TRB');
        $this->pdf->SetFont('', '');
        $this->pdf->Cell(0, 0, 'Firma:', 1, 1);

        $this->pdf->Cell(24, 6, 'Cliente: ', 'TLB', 0, 'R');
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->record->getProvider()->getName(), 'TRB', 1);

        $this->pdf->ln(2);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(0, 6, 'DATOS DEL SERVICIO', 1, 1, 'C');
        $this->pdf->Cell(40, 0, 'Fecha', 1);
        $this->pdf->Cell(0, 0, $this->record->getStartAt()->format('d/m/Y'), 1, 1);
        $this->pdf->Cell(40, 0, 'Hora', 1);
        $this->pdf->Cell(0, 0, $this->record->getStartAt()->format('H:i'), 1, 1);
        $this->pdf->Cell(40, 0, 'Origen', 1);
        $this->pdf->Cell(0, 0, $this->record->getStartPlace()->getName(), 1, 1);
        $this->pdf->Cell(40, 0, 'Destino', 1);
        $this->pdf->Cell(0, 0, $this->record->getEndPlace()->getName(), 1, 1);
        $this->pdf->Cell(40, 0, 'Pasajeros', 1);
        $this->pdf->Cell(0, 0, '', 1, 1);
        $this->pdf->Cell(40, 0, 'Tipo de servicio', 1);
        $this->pdf->Cell(0, 0, 'A disposición', 1, 1);
        $this->pdf->Cell(40, 0, 'Kms recorridos', 1);
        $this->pdf->Cell(30, 0, '', 1);
        $this->pdf->Cell(40, 0, 'Horas', 1);
        $this->pdf->Cell(0, 0, '', 1, 1);
        $this->pdf->Cell(0, 0, 'Precio pactado:', 1, 1);
        $this->pdf->Cell(40, 0, 'Kms adicionales', 1);
        $this->pdf->Cell(30, 0, '', 1);
        $this->pdf->Cell(40, 0, 'Horas adicionales', 1);
        $this->pdf->Cell(0, 0, '', 1, 1);
        $this->pdf->Cell(40, 0, 'Costo adicional', 1);
        $this->pdf->Cell(30, 0, '', 1);
        $this->pdf->Cell(40, 0, 'Precio final', 1);
        $this->pdf->Cell(0, 0, '', 1, 1);

        $this->pdf->Cell(0, 0, 'Observaciones', 'LTR', 1);
        $this->pdf->Cell(0, 0, '', 'LBR', 1, 1);
    }

    private function renderFooter()
    {
        $this->pdf->ln(2);
        $this->pdf->Cell(0, 0, 'Representante del cliente:', 1, 1);
        $this->pdf->Cell(0, 0, '', 'LTR', 1);
        $this->pdf->Cell(0, 0, 'Nombres y apellidos:', 'LBR', 1);
        $this->pdf->Cell(50, 0, '', 'LTR');
        $this->pdf->Cell(0, 0, '', 'LTR', 1);
        $this->pdf->Cell(50, 0, 'CI:', 'LBR');
        $this->pdf->Cell(0, 0, 'Firma:', 'LBR', 1);
    }
}