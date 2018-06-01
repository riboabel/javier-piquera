<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Provider;

class ServicesByProviderReport extends Report
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
     * @var boolean
     */
    private $showLogo;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @param array $parameeters
     * @param EntityManager $manager
     * @param string $logoPath
     */
    public function __construct($parameeters, EntityManager $manager, $logoPath)
    {
        parent::__construct('L', 'A4');

        $this->start = $parameeters['fromDate'];
        $this->end = $parameeters['toDate'];
        $this->provider = $parameeters['provider'];
        $this->services = $parameeters['services']->toArray();
        $this->showLogo = false;

        $this->manager = $manager;

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
        $this->pdf->Cell(20, 0, 'Cliente', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Nombre(s)', 1, 0, 'C');
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

            if ($h < 15 && null !== $record->getProvider()->getLogoName() && $this->showLogo) {
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
            if (null !== $record->getProvider()->getLogoName() && $this->showLogo) {
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
            $this->pdf->MultiCell(0, $h, $record->getDriver() ? $record->getDriver()->getName() : '', 1, 'L', false, 1);

            if (null !== $record->getServiceDescription()) {
                $this->pdf->SetFont('', 'B', 8);
                $this->pdf->MultiCell(0, 0, 'Descripción del servicio', 'LR', 'L', false, 1);
                $this->pdf->SetFont('', '');

                $x = $this->pdf->GetX();
                $y = $this->pdf->GetY();
                $this->pdf->writeHTMLCell(0, 0, $x, $y, $record->getServiceDescription(), 'LBR', 1);

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
        $qb = $this->manager->getRepository('AppBundle:Reserva')
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
