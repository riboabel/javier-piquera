<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Reserva;

/**
 * Description of ServicesBySelectionSpecialReport
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ServicesBySelectionSpecialReport extends Report
{
    /**
     * @var array
     */
    private $ids;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(array $ids, EntityManager $em)
    {
        parent::__construct('L', 'A4');

        $this->ids = $ids;

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
        $this->pdf->SetFont('Helvetica', 'B', 12);

        $this->pdf->Cell(0, 0, 'PROGRAMAS', 1, 1, 'C');

        $this->pdf->Cell(20, 0, 'Desde', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'Hasta', 1, 0, 'C');
        $this->pdf->Cell(10, 0, 'Días', 1, 0, 'C');
        $this->pdf->Cell(25, 0, 'Referencia', 1, 0, 'C');
        $this->pdf->Cell(55, 0, 'Clientes', 1, 0, 'C');
        $this->pdf->Cell(10, 0, 'Pax', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Origen', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Destino', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Localidades', 1, 1, 'C');
    }

    private function render()
    {
        $this->pdf->SetFont('Helvetica', '', 10);

        foreach ($this->getQuery()->getResult() as $record) {
            $h = $this->getRowHeight(array(
                array(20, $record->getStartAt()->format('d/m/Y')),
                array(20, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y') : ''),
                array(10, $record->countDays()),
                array(25, $record->getProviderReference()),
                array(55, $record->getClientNames()),
                array(10, $record->getPax()),
                array(30, $record->getStartPlace()->getName()),
                array(30, $record->getEndPlace()->getName()),
                array(0, $this->getLocationsText($record))
            ));

            $this->pdf->MultiCell(20, $h, $record->getStartAt()->format('d/m/Y'), 1, 'L', false, 0);
            $this->pdf->MultiCell(20, $h, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y') : '', 1, 'L', false, 0);
            $this->pdf->MultiCell(10, $h, $record->countDays(), 1, 'L', false, 0);
            $this->pdf->MultiCell(25, $h, $record->getProviderReference(), 1, 'L', false, 0);
            $this->pdf->MultiCell(55, $h, $record->getClientNames(), 1, 'L', false, 0);
            $this->pdf->MultiCell(10, $h, $record->getPax(), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getStartPlace()->getName(), 1, 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $record->getEndPlace()->getName(), 1, 'L', false, 0);
            $this->pdf->MultiCell(0, $h, $this->getLocationsText($record), 1, 'L', false, 1);
        }
    }

    private function getQuery()
    {
        $qb = $this->em->getRepository('AppBundle:Reserva')
            ->createQueryBuilder('r')
            ->leftJoin('r.driver', 'd')
            ->orderBy('r.startAt');

        if ($this->ids) {
            $qb->where($qb->expr()->in('r.id', $this->ids));
        }

        return $qb->getQuery();
    }

    private function getLocationsText(Reserva $record)
    {
        $lines = array();
        foreach ($record->getPassingPlaces() as $pp) {
            if (!array_key_exists($pp->getPlace()->getId(), $lines)) {
                $lines[$pp->getPlace()->getId()] = array(
                    'from' => $pp->getStayAt()->format('d/m'),
                    'to' => $pp->getStayAt()->format('d/m'),
                    'text' => $pp->getPlace()->getLocation() ? $pp->getPlace()->getLocation()->getName() : $pp->getPlace()->getName(),
                    'count' => 1
                );
            } else {
                $lines[$pp->getPlace()->getId()]['count']++;
                $lines[$pp->getPlace()->getId()]['to'] = $pp->getStayAt()->format('d/m');
            }
        }

        return implode("\n", array_map(function($line) {
            $dates = $line['count'] > 1 ? sprintf('%s - %s', $line['from'], $line['to']) : $line['from'];
            return sprintf('%s: %s', $dates, $line['text']);
        }, $lines));
    }
}
