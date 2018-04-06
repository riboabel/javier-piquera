<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Driver;
use AppBundle\Entity\ServiceType;
use Doctrine\ORM\QueryBuilder;

class ServicesByDriverReport extends Report
{
    /**
     * @var boolean
     */
    private $includePlacesAddress;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var boolean
     */
    private $showLogo;

    /**
     * ServicesByDriverReport constructor.
     * @param QueryBuilder  $queryBuilder
     * @param array         $parameters
     * @param string        $logoPath
     */
    public function __construct(QueryBuilder $queryBuilder, array $parameters, $logoPath)
    {
        parent::__construct('L', 'A4');

        $this->includePlacesAddress = $parameters['includePlacesAddress'];
        $this->showLogo = $parameters['showProviderLogoIfPossible'];

        $this->queryBuilder = $queryBuilder;

        $this->logoPath = $logoPath;
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->renderHeader();
        $this->render();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFont('Helvetica', 'B', 13);

        $this->pdf->Cell(24, 0, 'Número', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Inicio', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Fin', 1, 0, 'C');
        $this->pdf->Cell(25, 0, 'Referencia', 1, 0, 'C');
        $this->pdf->Cell(61, 0, 'Servicio', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'Agencia', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Clientes', 1, 0, 'C');
        $this->pdf->Cell(10, 0, 'Pax', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'Guía', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Conductor', 1, 1, 'C');
    }

    private function render()
    {
        $this->pdf->SetFont('Helvetica', '', 10);

        foreach ($this->getQuery()->getResult() as $record) {
            $h = $this->getRowHeight(array(
                array(24, $record->getSerialNumber()),
                array(30, $record->getStartAt()->format('d/m/Y H:i')),
                array(30, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : ''),
                array(25, $record->getProviderReference()),
                array(61, $record->getServiceType()->getName()),
                array(20, (null !== $record->getProvider()->getLogoName()) && $this->showLogo ? '' : $record->getProvider()->getName()),
                array(30, $record->getClientNames()),
                array(10, $record->getPax()),
                array(20, $record->getGuide() ? $record->getGuide()->getName() : ''),
                array(0, $record->getDriver() ? $record->getDriver()->getName() : '')
            ));

            if ($this->showLogo && ($h < 15) && (null !== $record->getProvider()->getLogoName())) {
                $h = 15;
            }

            if ($this->pdf->GetY() + $h > $this->pdf->getPageHeight() - $this->pdf->getMargins()['bottom']) {
                $this->pdf->AddPage();
            }

            $this->pdf->MultiCell(24, $h, $record->getSerialNumber(), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getStartAt()->format('d/m/Y H:i'), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : '', 1, 'L', false, 0);
            $this->pdf->MultiCell(25, $h, $record->getProviderReference(), 1, 'L', false, 0);
            $this->pdf->MultiCell(61, $h, $record->getServiceType()->getName(), 1, 'L', false, 0);
            if ($this->showLogo && (null !== $record->getProvider()->getLogoName())) {
                $imagePath = sprintf('%s/%s', $this->logoPath, $record->getProvider()->getLogoName());
                $x = $this->pdf->GetX();
                $y = $this->pdf->GetY();
                $this->pdf->MultiCell(20, $h, '', 1, 'L', false, 0);
                $this->pdf->Image($imagePath, $x + 0.5, $y + 0.5, 19, $h - 1, '', '', '', 2, 300, '', false, false, 0, 'CM');
            } else {
                $this->pdf->MultiCell(20, $h, $record->getProvider()->getName(), 1, 'L', false, 0);
            }
            $this->pdf->MultiCell(30, $h, $record->getClientNames(), 1, 'L', false, 0);
            $this->pdf->MultiCell(10, $h, $record->getPax(), 1, 'C', false, 0);
            $this->pdf->MultiCell(20, $h, $record->getGuide() ? $record->getGuide()->getName() : '', 1, 'L', false, 0);
            $record->getIsDriverConfirmed() ? $this->pdf->SetTextColor(60, 118, 61) : $this->pdf->SetTextColor(169, 68, 66);
            $this->pdf->MultiCell(0, $h, $record->getDriver() ? $record->getDriver()->getName() : '', 1, 'L', false, 1);
            $this->pdf->SetTextColor(0);

            if (null !== $record->getServiceDescription()) {
                $this->pdf->SetFont('', 'B', 8);
                $this->pdf->MultiCell(0, 0, 'Descripción del servicio', 'LR', 'L', false, 1);
                $this->pdf->SetFont('', '');

                $x = $this->pdf->GetX();
                $y = $this->pdf->GetY();

                $this->pdf->writeHTMLCell(0, 0, $x, $y, $record->getServiceDescription(), 'LBR', 1);
                $this->pdf->SetFont('', '', 10);
            }

            if ($this->includePlacesAddress) {
                $h = $this->getRowHeight(array(
                    array(16, 'Desde'),
                    array(30, $record->getStartPlace()->getName()),
                    array(80, $record->getStartPlace()->getPostalAddress()),
                    array(16, 'Hasta'),
                    array(30, $record->getEndPlace()->getName()),
                    array(0, $record->getEndPlace()->getPostalAddress())
                ));

                $border = $record->getPassingPlaces()->count() > 0 ? 'LTR' : 1;
                $this->pdf->MultiCell(24, $h, 'Desde', $border, 'L', false, 0);
                $this->pdf->MultiCell(30, $h, $record->getStartPlace()->getName(), $border, 'L', false, 0);
                $this->pdf->MultiCell(80, $h, $record->getStartPlace()->getPostalAddress(), $border, 'L', false, 0);
                $this->pdf->MultiCell(24, $h, 'Hasta', $border, 'L', false, 0);
                $this->pdf->MultiCell(30, $h, $record->getEndPlace()->getName(), $border, 'L', false, 0);
                $this->pdf->MultiCell(0, $h, $record->getEndPlace()->getPostalAddress(), $border, 'L', false, 1);

                foreach ($record->getPassingPlaces() as $i => $place) {
                    $h = $this->getRowHeight(array(
                        array(20, $place->getStayAt()->format('d/m/Y')),
                        array(60, $place->getPlace()->getName()),
                        array(0, $place->getPlace()->getPostalAddress())
                    ));

                    $border = $i == $record->getPassingPlaces()->count() - 1 ? 'LBR' : 'LR';

                    $this->pdf->MultiCell(20, $h, $place->getStayAt()->format('d/m/Y'), $border, 'L', false, 0);
                    $this->pdf->MultiCell(60, $h, $place->getPlace()->getName(), $border, 'L', false, 0);
                    $this->pdf->MultiCell(0, $h, $place->getPlace()->getPostalAddress(), $border, 'L', false, 1);
                }
            }

             if (null !== $record->getGuide() && $record->getGuide()->getContactInfo()) {
                $this->pdf->SetFont('', 'B', 8);
                $this->pdf->MultiCell(0, 0, sprintf('Dirección postal de %s', $record->getGuide()->getName()), 'LR', 'L', false, 1);
                $this->pdf->SetFont('', '');
                $this->pdf->MultiCell(0, 0, $record->getGuide()->getContactInfo(), 'LBR', 'L', false, 1);
                $this->pdf->SetFont('', '', 10);
            }

            $this->pdf->setDrawColorArray(array(255, 10, 10));
            $this->pdf->SetFillColorArray(array(255, 10, 10));
            $this->pdf->Cell(0, 0.5, '', 'LRB', 1, 'C', true, '', 0, true);
            $this->pdf->setDrawColorArray(array(0, 0, 0));
            $this->pdf->SetFillColorArray(array(0, 0, 0));

            $this->pdf->ln(1);
        }
    }

    private function getQuery()
    {
        return $this->queryBuilder->getQuery();
    }
}
