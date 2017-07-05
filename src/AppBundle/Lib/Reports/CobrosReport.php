<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;

class CobrosReport extends Report
{
    /**
     * @var \DateTime
     */
    private $startAt;

    /**
     * @var \DateTime
     */
    private $endAt;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em, \DateTime $start = null, \DateTime $end = null)
    {
        parent::__construct('L', 'A4');

        $this->startAt = $start;
        $this->endAt = $end;
        $this->em = $em;
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
    }

    private function render()
    {
        $this->pdf->SetFont('', '', 10);

        foreach ($this->getQuery()->getResult() as $record) {
            $this->pdf->Cell(25, 0, $record->getCreatedAt()->format('d/m/Y'), 1);
            $this->pdf->Cell(0, 0, sprintf('%s servicio(s) cobrado(s)', $record->getCharges()->count()), 1, 1);

            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell(25, 0, '', 'R');
            $this->pdf->Cell(30, 0, 'Inicio', 'BR', 0, 'C');
            $this->pdf->Cell(30, 0, 'Fin', 'BR', 0, 'C');
            $this->pdf->Cell(25, 0, 'Referencia', 'BR', 0, 'C');
            $this->pdf->Cell(50, 0, 'Agencia', 'BR', 0, 'C');
            $this->pdf->Cell(50, 0, 'Servicio', 'BR', 0, 'C');
            $this->pdf->Cell(50, 0, 'Conductor', 'BR', 0, 'C');
            $this->pdf->Cell(0, 0, 'Importe', 'BR', 1, 'R');

            $this->pdf->SetFont('', '');
            foreach ($record->getCharges() as $charge) {
                $this->pdf->Cell(25, 0, '', 'R');
                $this->pdf->Cell(30, 0, $charge->getStartAt()->format('d/m/Y H:i'), 'BR', 0);
                $this->pdf->Cell(30, 0, $charge->getEndAt() ? $charge->getEndAt()->format('d/m/Y H:i') : '', 'BR', 0);
                $this->pdf->Cell(25, 0, $charge->getProviderReference(), 'BR', 0);
                $this->pdf->Cell(50, 0, $charge->getProvider()->getName(), 'BR', 0);
                $this->pdf->Cell(50, 0, $charge->getServiceType()->getName(), 'BR', 0);
                $this->pdf->Cell(50, 0, $charge->getDriver() ? $charge->getDriver()->getName() : null, 'BR');
                $this->pdf->Cell(0, 0, sprintf('%0.2f', $charge->getClientPriceAmount()), 'BR', 1, 'R');
            }

            $this->pdf->ln(1);
        }
    }

    private function renderFooter()
    {

    }

    private function getQuery()
    {
        $qb = $this->em->getRepository('AppBundle:ChargeAct')
                ->createQueryBuilder('c')
                ->orderBy('c.createdAt');

        $andX = $qb->expr()->andX();

        if (null !== $this->startAt) {
            $andX->add($qb->expr()->gte('c.createdAt', $qb->expr()->literal($this->startAt->format('Y-m-d'))));
        }
        if (null !== $this->endAt) {
            $andX->add($qb->expr()->lte('c.createdAt', $qb->expr()->literal($this->endAt->format('Y-m-d 23:59:59'))));
        }

        if (0 < $andX->count()) {
            $qb->where($andX);
        }

        return $qb->getQuery();
    }
}