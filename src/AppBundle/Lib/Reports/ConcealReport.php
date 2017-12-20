<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Provider;

class ConcealReport extends Report
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
     * @var Provider
     */
    private $provider;

    /**
     * @var array
     */
    private $services;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct($start, $end, Provider $provider, array $services, EntityManager $em)
    {
        parent::__construct('L', 'A4');

        $this->start = $start;
        $this->end = $end;
        $this->provider = $provider;
        $this->services = $services;

        $this->em = $em;
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
                array(20, $record->getProvider()->getName()),
                array(30, $record->getClientNames()),
                array(10, $record->getPax()),
                array(20, $record->getGuide() ? $record->getGuide()->getName() : ''),
                array(0, $record->getDriver() ? $record->getDriver()->getName() : '')
            ));

            $this->pdf->MultiCell(24, $h, $record->getSerialNumber(), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getStartAt()->format('d/m/Y H:i'), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : '', 1, 'L', false, 0);
            $this->pdf->MultiCell(25, $h, $record->getProviderReference(), 1, 'L', false, 0);
            $this->pdf->MultiCell(61, $h, $record->getServiceType()->getName(), 1, 'L', false, 0);
            $this->pdf->MultiCell(20, $h, $record->getProvider()->getName(), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getClientNames(), 1, 'L', false, 0);
            $this->pdf->MultiCell(10, $h, $record->getPax(), 1, 'C', false, 0);
            $this->pdf->MultiCell(20, $h, $record->getGuide() ? $record->getGuide()->getName() : '', 1, 'L', false, 0);
            $this->pdf->MultiCell(0, $h, $record->getDriver() ? $record->getDriver()->getName() : '', 1, 'L', false, 1);

            if (null !== $record->getServiceDescription()) {
                $this->pdf->SetFont('', 'B', 8);
                $this->pdf->MultiCell(0, 0, 'Descripción del servicio', 'LR', 'L', false, 1);
                $this->pdf->SetFont('', '');
                $this->pdf->MultiCell(0, 0, $record->getServiceDescription(), 'LBR', 'L', false, 1);
                $this->pdf->SetFont('', '', 10);
            }

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
            ->join('r.provider', 'p')
            ->join('r.serviceType', 'st')
            ->orderBy('r.startAt');

        $andX = $qb->expr()->andX(
            $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false)),
            $qb->expr()->eq('p.id', $qb->expr()->literal($this->provider->getId()))
        );

        if ($this->start) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($this->start->format('Y-m-d'))));
        }
        if ($this->end) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($this->end->format('Y-m-d 23:59:59'))));
        }

        if ($this->services) {
            $andX->add($qb->expr()->in('st.id', array_map(function(\AppBundle\Entity\ServiceType $service) {return $service->getId();}, $this->services)));
        }

        $qb->where($andX);

        return $qb->getQuery();
    }
}
