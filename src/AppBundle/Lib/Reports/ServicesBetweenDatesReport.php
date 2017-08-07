<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;

class ServicesBetweenDatesReport extends Report
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
     * @var boolean
     */
    private $includePlacesAddress;

    /**
     * @var array
     */
    private $services;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $logoPath;

    public function __construct($start, $end, $includePlacesAddress, array $services, EntityManager $em, $logoPath)
    {
        parent::__construct('L', 'A4');

        $this->start = $start;
        $this->end = $end;
        $this->includePlacesAddress = $includePlacesAddress;
        $this->services = $services;
        $this->em = $em;
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
        $this->pdf->Cell(46, 0, 'Servicio', 1, 0, 'C');
        $this->pdf->Cell(25, 0, 'Agencia', 1, 0, 'C');
        $this->pdf->Cell(40, 0, 'Clientes', 1, 0, 'C');
        $this->pdf->Cell(10, 0, 'Pax', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'Guía', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Conductor', 1, 1, 'C');
    }

    private function render()
    {
        $this->pdf->SetFont('Helvetica', '', 10);

        $records = $this->getQuery()->getResult();
        foreach ($records as $record) {
            $h = $this->getRowHeight(array(
                array(24, $record->getSerialNumber()),
                array(30, $record->getStartAt()->format('d/m/Y H:i')),
                array(30, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : ''),
                array(25, $record->getProviderReference()),
                array(46, $record->getServiceType()->getName()),
                array(25, null !== $record->getProvider()->getLogoName() ? '' : $record->getProvider()->getName()),
                array(40, $record->getClientNames()),
                array(10, $record->getPax()),
                array(20, $record->getGuide() ? $record->getGuide()->getName() : ''),
                array(0, $record->getDriver() ? $record->getDriver()->getName() : '')
            ));

            if ($h < 20 && null !== $record->getProvider()->getLogoName()) {
                $h = 20;
            }
            
            if ($this->pdf->GetY() + $h > $this->pdf->getPageHeight() - $this->pdf->getMargins()['bottom']) {
                $this->pdf->AddPage();
            }

            $this->pdf->MultiCell(24, $h, $record->getSerialNumber(), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getStartAt()->format('d/m/Y H:i'), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : '', 1, 'L', false, 0);
            $this->pdf->MultiCell(25, $h, $record->getProviderReference(), 1, 'L', false, 0);
            $this->pdf->MultiCell(46, $h, $record->getServiceType()->getName(), 1, 'L', false, 0);
            if (null !== $record->getProvider()->getLogoName()) {
                $this->pdf->writeHTMLCell(25, $h, $this->pdf->GetX(), $this->pdf->GetY(), sprintf('<img src="%s" width="42", height="42"/>', sprintf('%s/%s', $this->logoPath, $record->getProvider()->getLogoName())), 1, 0, false, true, 'C');
            } else {
                $this->pdf->MultiCell(25, $h, $record->getProvider()->getName(), 1, 'L', false, 0);
            }
            $this->pdf->MultiCell(40, $h, $record->getClientNames(), 1, 'L', false, 0);
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
                    array(20, 'Desde'),
                    array(40, $record->getStartPlace()->getName()),
                    array(80, $record->getStartPlace()->getPostalAddress()),
                    array(20, 'Hasta'),
                    array(40, $record->getEndPlace()->getName()),
                    array(0, $record->getEndPlace()->getPostalAddress())
                ));

                $border = $record->getPassingPlaces()->count() > 0 ? 'LTR' : 1;
                $this->pdf->MultiCell(20, $h, 'Desde', $border, 'L', false, 0);
                $this->pdf->MultiCell(40, $h, $record->getStartPlace()->getName(), $border, 'L', false, 0);
                $this->pdf->MultiCell(80, $h, $record->getStartPlace()->getPostalAddress(), $border, 'L', false, 0);
                $this->pdf->MultiCell(20, $h, 'Hasta', $border, 'L', false, 0);
                $this->pdf->MultiCell(40, $h, $record->getEndPlace()->getName(), $border, 'L', false, 0);
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
        $qb = $this->em->getRepository('AppBundle:Reserva')
            ->createQueryBuilder('r')
            ->orderBy('r.startAt');

        $andX = $qb->expr()->andX($qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false)));

        if ($this->start) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($this->start->format('Y-m-d 00:00:00'))));
        }
        if ($this->end) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($this->end->format('Y-m-d 23:59:59'))));
        }

        if ($this->services) {
            $qb->join('r.serviceType', 'st');
            $andX->add($qb->expr()->in('st.id', array_map(function($service) {return $service->getId();}, $this->services)));
        }

        $qb->where($andX);

        return $qb->getQuery();
    }
}
