<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Driver;
use AppBundle\Entity\ServiceType;

class ServicesByDriverReport extends Report
{
    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var array<Driver>
     */
    private $drivers;

    /**
     * @var boolean
     */
    private $includePlacesAddress;

    /**
     * @var ServiceType
     */
    private $serviceType;

    /**
     * @var EntityManager
     */
    private $em;

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
     * @param string $parameters
     * @param EntityManager $em
     * @param $logoPath
     */
    public function __construct($parameters, EntityManager $em, $logoPath)
    {
        parent::__construct('L', 'A4');

        $this->start = $parameters['fromDate'];
        $this->end = $parameters['toDate'];
        $this->drivers = $parameters['drivers'];
        $this->includePlacesAddress = $parameters['includePlacesAddress'];
        $this->serviceType = $parameters['serviceType'];
        $this->showLogo = $parameters['showProviderLogoIfPossible'];

        $this->em = $em;

        $this->logoPath = $logoPath;
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->renderHeader();
        $this->render();
        $this->renderFooter();

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
                $this->pdf->MultiCell(0, 0, $record->getServiceDescription(), 'LBR', 'L', false, 1);
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

    private function renderFooter()
    {

    }

    private function getQuery()
    {
        $qb = $this->em->getRepository('AppBundle:Reserva')
            ->createQueryBuilder('r')
            ->join('r.driver', 'd')
            ->orderBy('r.startAt');

        $andX = $qb->expr()->andX(
            $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false)),
            $qb->expr()->in('d.id', array_map(function(Driver $driver) {return $driver->getId();}, $this->drivers->toArray()))
        );

        if ($this->start) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($this->start->format('Y-m-d'))));
        }
        if ($this->end) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($this->end->format('Y-m-d 23:59:59'))));
        }

        if ($this->serviceType->count() > 0) {
            $qb->join('r.serviceType', 's');
            $andX->add($qb->expr()->in('s.id', array_map(function($service) {
                return $service->getId();
            }, $this->serviceType->toArray())));
        }

        $qb->where($andX);

        return $qb->getQuery();
    }
}
