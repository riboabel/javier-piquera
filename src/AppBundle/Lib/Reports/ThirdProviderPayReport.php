<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 11/11/2018
 * Time: 2:07 PM
 */

namespace AppBundle\Lib\Reports;

use AppBundle\Entity\ReservaTercero;
use AppBundle\Entity\ThirdPayAct;

class ThirdProviderPayReport extends Report
{
    /**
     * @var ThirdPayAct
     */
    private $record;

    /**
     * @var ReservaTercero[]
     */
    private $services;

    public function __construct(ThirdPayAct $act)
    {
        $this->record = $act;

        parent::__construct('P', 'LETTER');

        $this->loadServices();
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->pdf->SetFontSize(10);

        $this->renderHeader();
        $this->renderBody();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->Cell(20, 0, 'Fecha', 0, 0, 'R');
        $this->pdf->Cell(0, 0, $this->getPayDate()->format('d/m/Y H:i'), 0, 1, 'L');
        $this->pdf->Cell(20, 0, 'Beneficiario', 0, 0, 'R');
        $this->pdf->Cell(0, 0, $this->getPayProvider()->getName(), 0, 1, 'L');
        $this->pdf->ln(4);
    }

    private function renderBody()
    {
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(32, 0, 'Fecha', 1, 0, 'C');
        $this->pdf->Cell(100, 0, 'Servicio', 1, 0, 'C');
        $this->pdf->Cell(40, 0, 'Referencia', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Importe', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $totalPrice = 0;
        foreach ($this->services as $service) {
            $this->pdf->Cell(32, 0, $service->getStartAt()->format('d/m/Y H:i'), 1, 0);
            $this->pdf->Cell(100, 0, (string) $service->getServiceType(), 1, 0);
            $this->pdf->Cell(40, 0, (string) $service->getClientSerial(), 1, 0);
            $this->pdf->Cell(0, 0, sprintf('%0.2f', $service->getPaidCharge()), 1, 1, 'R');

            $totalPrice += $service->getPaidCharge();

            if ($service->getPayNotes()) {
                $fontSize = $this->pdf->getFontSizePt();
                $this->pdf->SetFontSize(8);
                $this->pdf->Cell(0, 0, $service->getPayNotes(), 1, 1, 'J');
                $this->pdf->SetFontSize($fontSize);
            }
        }

        $this->pdf->Cell(172, 0, 'Total', 1, 0);
        $this->pdf->Cell(0, 0, sprintf('%0.2f', $totalPrice), 1, 1, 'R');
    }

    private function getPayDate()
    {
        return $this->record->getCreatedAt();
    }

    private function getPayProvider()
    {
        /** @var ReservaTercero[] $services */
        $services = $this->record->getServices();

        return $services[0]->getProvider();
    }

    private function loadServices()
    {
        $this->services = $this->record->getServices();
    }
}