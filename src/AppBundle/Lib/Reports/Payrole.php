<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;

class Payrole extends Report
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $logoPath;

    public function __construct(array $params, EntityManager $em, $logoPath)
    {
        parent::__construct('P', 'LETTER');

        $this->em = $em;

        $this->params = $params;
        $this->logoPath = $logoPath;
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
        $this->pdf->SetFont('Helvetica', 'B', 12);
        $this->pdf->Cell(0, 0, 'MODELO DE PAGO', 1, 1, 'C');
        $this->pdf->ln(2);

        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(20, 0, sprintf('Fecha de impresión: %s', date('d/m/Y')), 0, 1, 'L');
        $this->pdf->MultiCell(0, 0, sprintf('Conductor(es): %s', $this->getDriversLine()), 0, 'L');
        $this->pdf->ln(4);
    }

    private function renderBody()
    {
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(24, 0, 'NÚMERO', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'FECHA', 1, 0, 'C');
        $this->pdf->Cell(40, 0, 'SERVICIO', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'AGENCIA', 1, 0, 'C');
        $this->pdf->Cell(46, 0, 'CLIENTE', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'IMPORTE', 1, 1, 'C');

        $this->pdf->SetFont('Helvetica', '', 10);
        foreach ($this->getData() as $data) {
            $h = $this->getRowHeight(array(
                array(24, $data['record']->getSerialNumber()),
                array(20, $data['record']->getStartAt()->format('d/m/Y')),
                array(40, $data['record']->getServiceType()->getName()),
                array(30, $data['record']->getProvider()->getName()),
                array(46, $data['record']->getClientNames()),
                array(0, sprintf('%0.2f', $data['price']))
            ));

            if (null !== $data['record']->getProvider()->getLogoName()) {
                if ($h < 20) {
                    $h = 20;
                }
            }

            $this->pdf->MultiCell(24, $h, $data['record']->getSerialNumber(), 'LBR', 'C', false, 0);
            $this->pdf->MultiCell(20, $h, $data['record']->getStartAt()->format('d/m/Y'), 'BR', 'L', false, 0);
            $this->pdf->MultiCell(40, $h, $data['record']->getServiceType()->getName(), 'BR', 'L', false, 0);
            if (null !== $data['record']->getProvider()->getLogoName()) {
                $imagePath = sprintf('%s/%s', $this->logoPath, $data['record']->getProvider()->getLogoName());
                $x = $this->pdf->GetX();
                $y = $this->pdf->GetY();
                $this->pdf->MultiCell(30, $h, '', 'BR', 'L', false, 0);
                $this->pdf->Image($imagePath, $x + 0.5, $y + 0.5, 28, $h - 1, '', '', '', 2, 300, '', false, false, 0, 'CM');
            } else {
                $this->pdf->MultiCell(30, $h, $data['record']->getProvider()->getName(), 'BR', 'L', false, 0);
            }
            $this->pdf->MultiCell(46, $h, $data['record']->getClientNames(), 'BR', 'L', false, 0);
            $this->pdf->MultiCell(0, $h, sprintf('%0.2f', $data['price']), 'BR', 'R', false, 1);
        }

        $this->pdf->ln(4);
    }

    private function renderFooter()
    {
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(178, 0, 'Total a pagar', 0, 0, 'R');

        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(0, 0, sprintf('%0.2f', $this->getTotalPrice()), array('LBTR' => array('width' => 0.7)), 1, 'R');
    }

    private function getData()
    {
        if (null === $this->data) {
            $this->data = array();
            $query = $this->em->createQuery('SELECT r FROM AppBundle:Reserva r JOIN r.driver d JOIN r.provider p JOIN r.serviceType st WHERE r.id in (:ids) ORDER BY r.startAt')
                    ->setParameter('ids', $this->params['ids']);

            foreach ($query->getResult() as $record) {
                foreach ($this->params['ids'] as $k => $id) {
                    if ($id == $record->getId()) {
                        $this->data[$k] = array(
                            'record' => $record,
                            'price' => sprintf('%0.2f', isset($this->params['prices'][$k]) ? $this->params['prices'][$k] : $record->getDriverPayAmount())
                        );
                        break;
                    }
                }
            }
        }

        return $this->data;
    }

    private function getDriversLine()
    {
        $drivers = array();
        foreach ($this->getData() as $record) {
            $drivers[$record['record']->getDriver()->getId()] = $record['record']->getDriver()->getName();
        }

        return implode(', ', $drivers);
    }

    private function getTotalPrice()
    {
        $sum = 0;

        foreach ($this->getData() as $data) {
            $sum += $data['price'];
        }

        return $sum;
    }
}