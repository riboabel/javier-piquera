<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Invoice as Entity;
use AppBundle\Entity\InvoiceLine;
use Doctrine\ORM\NoResultException;
use AppBundle\Entity\Reserva;

class Invoice extends Report
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var Entity
     */
    private $record;

    /**
     * @var string
     */
    private $logoPath;

    public function __construct(array $params, EntityManager $manager)
    {
        parent::__construct('P', 'LETTER');

        $this->manager = $manager;

        $this->record = $params['record'];
        $this->logoPath = $params['logo_path'];
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFont('Helvetica', 'B', 15);
        $this->pdf->Cell(0, 15, 'FACTURA COMERCIAL', 1, 1, 'C');

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Acreedor', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 6, $this->record->getDriver(), 1);

        $this->pdf->Cell(40, 6, sprintf('NIT: %s', $this->record->getDriver()->getNit()), 1, 1);

        if ($this->record->getDriver()->getPostalAddress()) {
            $h = $this->getRowHeight(array(
                array(36, 'Dirección Acreedor'),
                array(120, $this->record->getDriver()->getPostalAddress())
            ));
            $this->pdf->SetFont('', '');
            $this->pdf->MultiCell(35, $h, 'Dirección Acreedor', 1, 'L', false, 0);
            $this->pdf->SetFont('', 'B');
            $this->pdf->MultiCell(120, $h, $this->record->getDriver()->getPostalAddress(), 1, 'L');
        } else {
            $this->pdf->Cell(155, 0, '', 1, 1);
        }

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, $this->record->getProvider()->getContractNumber() ? sprintf('Contrato # %s', $this->record->getProvider()->getContractNumber()) : '', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 6, 'Provincia: Habana', 1);

        $this->pdf->Cell(40, 6, sprintf('Factura # %s', $this->record->getSerialNumber()), 1, 1);

        if ($this->record->getModelName() === 'ATRIO') {
            $this->pdf->SetFont('', '');
            $this->pdf->Cell(155, 6, sprintf('Orden de trabajo de referencia %s', $this->getServiceFromLine($this->record->getLines()[0])->getSerialNumber()), 1, 1);
        } else {
            $this->pdf->Cell(155, 6, '', 1, 1);
        }

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(35, 6, 'Deudor', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 6, $this->record->getProvider()->getName(), 1);

        $this->pdf->SetFont('', '', 10);
        $this->pdf->Cell(40, 6, $this->record->getProvider()->getReeupCode() ? sprintf('REEUP: %s', $this->record->getProvider()->getReeupCode()) : '', 1, 1);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, 'Dirección Deudor', 1);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->record->getProvider()->getPostalAddress(), 1, 1);

        $this->pdf->ln(7);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, 'Pagar a favor de', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, $this->record->getDriver(), 1, 1);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, 'Cuenta bancaria', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(0, 6, sprintf('%s   Banco Metropolitano', $this->record->getDriver()->getBankAccount()), 1, 1);

        $this->pdf->ln(25);

        $this->pdf->SetFont('', 'B');
        if ('ATRIO' === $this->record->getModelName()) {
            $this->pdf->Cell(115, 10, 'Descripción del Servicio', 1, 0, 'C');
            $this->pdf->Cell(10, 10, 'UM', 1, 0, 'C');
            $this->pdf->Cell(23, 10, 'Cantidad', 1, 0, 'C');
            $this->pdf->Cell(23, 10, 'Precio CUC', 1, 0, 'C');
            $this->pdf->Cell(0, 10, 'Importe CUC', 1, 1, 'C');
        } else {
            $this->pdf->Cell(80, 10, 'Descripción del Servicio', 1, 0, 'C');
            $this->pdf->Cell(45, 10, 'Clientes', 1, 0, 'C');
            $this->pdf->Cell(23, 10, 'Referencia', 1, 0, 'C');
            $this->pdf->Cell(23, 10, 'NC', 1, 0, 'C');
            $this->pdf->Cell(0, 10, 'Importe CUC', 1, 1, 'C');
        }

        if (null !== $this->logoPath) {
            $this->pdf->Image($this->logoPath, 165, 27, 40, 25);
        }
    }

    private function renderBody()
    {
        $this->pdf->SetFont('', '');

        if ('ATRIO' === $this->record->getModelName()) {
            foreach ($this->record->getLines() as $line) {
                $this->pdf->Cell(115, 10, $line->getServiceName(), 1, 0);
                $this->pdf->Cell(10, 10, $line->getMeassurementUnit(), 'TBR', 0, 'C');
                $this->pdf->Cell(23, 10, sprintf('%0.1f', $line->getQuantity()), 'TBR', 0, 'C');
                $this->pdf->Cell(23, 10, sprintf('%0.2f', $line->getUnitPrice()), 'TBR', 0, 'C');
                $this->pdf->Cell(0, 10, sprintf('%0.2f', $line->getTotalPrice()), 'TBR', 1, 'C');
            }

            for ($i = 0; $i < 2; $i++) {
                $this->pdf->Cell(115, 10, '', 'LBR', 0);
                $this->pdf->Cell(10, 10, '', 'BR', 0, 'C');
                $this->pdf->Cell(23, 10, '', 'BR', 0, 'C');
                $this->pdf->Cell(23, 10, '', 'BR', 0, 'C');
                $this->pdf->Cell(0, 10, '', 'BR', 1, 'C');
            }

            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell(171, 10, 'Total', 1);
            $this->pdf->Cell(0, 10, sprintf('%0.2f', $this->record->getTotalCharge()), 1, 1, 'C');
        } else {
            foreach ($this->record->getLines() as $line) {
                $columns = array(
                    array(80, $line->getServiceName()),
                    array(45, $line->getClientsName()),
                    array(23, $line->getClientReference()),
                    array(23, $line->getServiceSerialNumber()),
                    array(0, sprintf('%0.2f', $line->getTotalPrice()))
                );
                $h = $this->getRowHeight($columns);
                $this->pdf->MultiCell(80, $h, $line->getServiceName(), 1, 'L', false, 0);
                $this->pdf->MultiCell(45, $h, $line->getClientsName(), 1, 'L', false, 0);
                $this->pdf->MultiCell(23, $h, $line->getClientReference(), 1, 'L', false, 0);
                $this->pdf->MultiCell(23, $h, $line->getServiceSerialNumber(), 1, 'L', false, 0);
                $this->pdf->MultiCell(0, $h, sprintf('%0.2f', $line->getTotalPrice()), 1, 'R');

                if ($line->getNotes()) {
                    $this->pdf->SetFont('', '', 8);
                    $this->pdf->MultiCell(0, 0, $line->getNotes(), 1, 'L');
                    $this->pdf->SetFont('', '', 10);
                }
            }

            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell(171, 10, 'Total', 1);
            $this->pdf->Cell(0, 10, sprintf('%0.2f', $this->record->getTotalCharge()), 1, 1, 'R');
        }
    }

    private function renderFooter()
    {
        $this->pdf->SetY(220, true);

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 6, '', 1);
        $this->pdf->Cell(80, 6, 'Acreedor', 1, 0, 'C');
        $this->pdf->Cell(0, 6, 'Deudor', 1, 1, 'C');

        $this->pdf->Cell(35, 8, 'Nombre', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 8, $this->record->getDriver(), 1, 0, 'C');
        $this->pdf->Cell(0, 8, '', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 8, 'Cargo', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 8, 'Chofer de Taxi Arrendado', 1, 0, 'C');
        $this->pdf->Cell(0, 8, '', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 8, 'Fecha', 1);
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(80, 8, $this->getServiceFromLine($this->record->getLines()[0])->getStartAt()->format('d/m/Y'), 1, 0, 'C');
        $this->pdf->Cell(0, 8, '', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $this->pdf->Cell(35, 9, 'Firma', 1);
        $this->pdf->Cell(80, 9, '', 1, 0, 'C');
        $this->pdf->Cell(0, 9, '', 1, 1, 'C');
    }

    /**
     * @param InvoiceLine $line
     * @return Reserva
     */
    private function getServiceFromLine(InvoiceLine $line)
    {
        $matches = array();
        preg_match('/^(T|t)(?P<year>\d{1})(?P<month>\d{2})(?P<day>\d{2})-(?P<id>(\d{2}|\d{4}))$/', $line->getServiceSerialNumber(), $matches);
        $date = new \DateTime(sprintf('%s%s-%s-%s', substr(date('y'), 0, 1), $matches['year'], $matches['month'], $matches['day']));

        $qb = $this->manager->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ;
        $andX = $qb->expr()->andX(
                $qb->expr()->gte('r.startAt', ':startAt'),
                $qb->expr()->lte('r.startAt', ':endAt')
                );
        $andX->add($qb->expr()->like('r.id', ':id'));
        if (2 === strlen($matches['id'])) {
            $andX->add($qb->expr()->lte('r.id', $qb->expr()->literal(2493)));
        }

        $qb
                ->where($andX)
                ->setParameters(array(
                    'startAt' => $date->format('Y-m-d 00:00:00'),
                    'endAt' => $date->format('Y-m-d 23:59:59'),
                    'id' => sprintf('%%%s', ltrim($matches['id'], '0'))
                ))
                ;

        try {
            $record = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $record = null;
        }

        return $record;
    }
}
