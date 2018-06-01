<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Entity\ReservaTercero;
use AppBundle\Entity\ThirdProvider;
use Doctrine\ORM\EntityManager;

/**
 * ServicesForThirdProviderReport
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ServicesForThirdProviderReport extends Report
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
     * @var ThirdProvider
     */
    private $provider;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var string
     */
    private $type;

    public function __construct($parameters, EntityManager $manager, $logoPath, $type)
    {
        parent::__construct('L', 'LETTER');

        $this->start = $parameters['fromDate'];
        $this->end = $parameters['toDate'];
        $this->provider = $parameters['provider'];

        $this->manager = $manager;

        $this->logoPath = $logoPath;
        $this->type = $type;
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

        $this->pdf->Cell(26, 0, 'Número', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Inicio', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'Fin', 1, 0, 'C');
        $this->pdf->Cell(25, 0, 'Referencia', 1, 0, 'C');
        $this->pdf->Cell(50, 0, 'Servicio', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'Cliente', 1, 0, 'C');
        $this->pdf->Cell(35, 0, 'Nombre(s)', 1, 0, 'C');
        $this->pdf->Cell(10, 0, 'Pax', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Proveedor', 1, 1, 'C');
    }

    private function render()
    {
        $this->pdf->SetFont('Helvetica', '', 10);

        foreach ($this->getQuery()->getResult() as $record) {
            $this->renderRecord($record);
        }
    }

    private function renderRecord(ReservaTercero $record)
    {
        $h = $this->getRowHeight(array(
            array(26, $this->getProviderSerialNumber($record)),
            array(30, $record->getStartAt()->format('d/m/Y H:i')),
            array(30, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : ''),
            array(25, $record->getClientSerial()),
            array(50, $record->getServiceType()->getName()),
            array(20, $record->getClient()->getName()),
            array(35, $record->getClientNames()),
            array(10, $record->getPax()),
            array(0, $record->getProvider()->getName())
        ));

        $this->pdf->MultiCell(26, $h, $this->getProviderSerialNumber($record), 1, 'L', false, 0);
        $this->pdf->MultiCell(30, $h, $record->getStartAt()->format('d/m/Y H:i'), 1, 'L', false, 0);
        $this->pdf->MultiCell(30, $h, $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : '', 1, 'L', false, 0);
        $this->pdf->MultiCell(25, $h, $record->getClientSerial(), 1, 'L', false, 0);
        $this->pdf->MultiCell(50, $h, $record->getServiceType()->getName(), 1, 'L', false, 0);
        $this->pdf->MultiCell(20, $h, $record->getClient()->getName(), 1, 'L', false, 0);
        $this->pdf->MultiCell(35, $h, $record->getClientNames(), 1, 'L', false, 0);
        $this->pdf->MultiCell(10, $h, $record->getPax(), 1, 'C', false, 0);
        $this->pdf->MultiCell(0, $h, $record->getProvider()->getName(), 1, 'L', false, 1);

        if (null !== $record->getServiceDescription()) {
            $this->pdf->SetFont('', 'B', 8);
            $this->pdf->MultiCell(0, 0, 'Descripción del servicio', 'LR', 'L', false, 1);
            $this->pdf->SetFont('', '');

            $x = $this->pdf->GetX();
            $y = $this->pdf->GetY();
            $this->pdf->writeHTMLCell(0, 0, $x, $y, $record->getServiceDescription(), 'LBR', 1);

            $this->pdf->SetFont('', '', 10);
        }

        $h = $this->getRowHeight(array(
            array(16, 'Desde'),
            array(30, $record->getStartIn()->getName()),
            array(80, $record->getStartIn()->getPostalAddress()),
            array(16, 'Hasta'),
            array(30, $record->getEndIn()->getName()),
            array(0, $record->getEndIn()->getPostalAddress())
        ));

        $border = 1;
        $this->pdf->MultiCell(24, $h, 'Desde', $border, 'L', false, 0);
        $this->pdf->MultiCell(30, $h, $record->getStartIn()->getName(), $border, 'L', false, 0);
        $this->pdf->MultiCell(80, $h, $record->getStartIn()->getPostalAddress(), $border, 'L', false, 0);
        $this->pdf->MultiCell(24, $h, 'Hasta', $border, 'L', false, 0);
        $this->pdf->MultiCell(30, $h, $record->getEndIn()->getName(), $border, 'L', false, 0);
        $this->pdf->MultiCell(0, $h, $record->getEndIn()->getPostalAddress(), $border, 'L', false, 1);

        $this->pdf->setDrawColorArray(array(255, 1, 80));
        $this->pdf->SetFillColorArray(array(255, 1, 80));
        $this->pdf->Cell(0, 0.5, '', 'LRB', 1, 'C', true, '', 0, true);
        $this->pdf->setDrawColorArray(array(0, 0, 0));
        $this->pdf->SetFillColorArray(array(0, 0, 0));

        $this->pdf->ln(1);
    }

    private function getProviderSerialNumber(ReservaTercero $record)
    {
        return ReservaTercero::TYPE_CLASICOS == $record->getType() ? (string)$record : $record->getProviderSerial();
    }

    private function renderFooter()
    {

    }

    private function getQuery()
    {
        $qb = $this->manager->getRepository('AppBundle:ReservaTercero')
            ->createQueryBuilder('r')
            ->join('r.provider', 'p')
            ->orderBy('r.startAt');

        $andX = $qb->expr()->andX(
            $qb->expr()->eq('p.id', ':provider'),
            $qb->expr()->neq('r.state', ':state'),
            $qb->expr()->eq('r.type', ':type')
        );

        if ($this->start) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($this->start->format('Y-m-d'))));
        }
        if ($this->end) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($this->end->format('Y-m-d 23:59:59'))));
        }


        $qb
            ->where($andX)
            ->setParameters(array(
                'state' => ReservaTercero::STATE_CANCELLED,
                'type' => $this->type,
                'provider' => $this->provider->getId()
            ));

        return $qb->getQuery();
    }
}
