<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;

class ChargeForm extends Report
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
        $this->pdf->Cell(0, 0, 'MODELO DE COBRO', 1, 1, 'C');
        $this->pdf->ln(2);

        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(20, 0, sprintf('Fecha de impresión: %s', date('d/m/Y')), 0, 1, 'L');
        $this->renderProvidersLine();
        $this->pdf->ln(4);
    }

    private function renderBody()
    {
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(24, 0, 'NÚMERO', 1, 0, 'C');
        $this->pdf->Cell(30, 0, 'REFERENCIA', 1, 0, 'C');
        $this->pdf->Cell(20, 0, 'FECHA', 1, 0, 'C');
        $this->pdf->Cell(60, 0, 'CLIENTE', 1, 0, 'C');
        $this->pdf->Cell(45, 0, 'SERVICIO', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'IMPORTE', 1, 1, 'C');

        $this->pdf->SetFont('Helvetica', '', 10);
        foreach ($this->getData() as $data) {
            $h = $this->getRowHeight(array(
                array(24, $data['record']->getSerialNumber()),
                array(30, $data['record']->getProviderReference()),
                array(20, $data['record']->getStartAt()->format('d/m/Y')),
                array(60, $data['record']->getClientNames()),
                array(45, $data['record']->getServiceType()->getName()),
                array(0, sprintf('%0.2f', $data['price']))
            ));
            
            $this->pdf->MultiCell(24, $h, $data['record']->getSerialNumber(), 'LBR', 'L', false, 0);
            $this->pdf->MultiCell(30, $h, $data['record']->getProviderReference(), 'BR', 'L', false, 0);
            $this->pdf->MultiCell(20, $h, $data['record']->getStartAt()->format('d/m/Y'), 'BR', 'L', false, 0);
            $this->pdf->MultiCell(60, $h, $data['record']->getClientNames(), 'BR', 'L', false, 0);
            $this->pdf->MultiCell(45, $h, $data['record']->getServiceType()->getName(), 'BR', 'L', false, 0);
            $this->pdf->MultiCell(0, $h, sprintf('%0.2f', $data['price']), 'BR', 'R', false, 1);
        }

        $this->pdf->ln(4);
    }

    private function renderFooter()
    {
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(178, 0, 'Total a cobrar', 0, 0, 'R');

        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(0, 0, sprintf('%0.2f', $this->getTotalPrice()), array('LBTR' => array('width' => 0.7)), 1, 'R');
    }

    private function getData()
    {
        if (null === $this->data) {
            $this->data = array();
            $query = $this->em->createQuery('SELECT r FROM AppBundle:Reserva r JOIN r.provider p JOIN r.serviceType st WHERE r.id in (:ids) ORDER BY r.startAt')
                    ->setParameter('ids', $this->params['ids']);

            foreach ($query->getResult() as $record) {
                foreach ($this->params['ids'] as $k => $id) {
                    if ($id == $record->getId()) {
                        $this->data[$k] = array(
                            'record' => $record,
                            'price' => isset($this->params['prices'][$k]) ? $this->params['prices'][$k] : sprintf('%0.2f', $record->getClientPriceAmount()
                        ));
                        break;
                    }
                }
            }
        }

        return $this->data;
    }

    private function renderProvidersLine()
    {
        $withLogo = array();
        $withoutLogo = array();

        foreach ($this->getData() as $record) {
            if (null !== $record['record']->getProvider()->getLogoName()) {
                $withLogo[$record['record']->getProvider()->getId()] = $record['record']->getProvider();
            } else {
                $withoutLogo[$record['record']->getProvider()->getId()] = $record['record']->getProvider();
            }
        }

        $this->pdf->MultiCell(25, 0, 'Agencia(s):', 0, 'L', false, 0);
        
        if ($withLogo) {
            $x = $this->pdf->GetX();
            $y = $this->pdf->GetY();

            $i = 0;
            foreach ($withLogo as $provider) {
                $imagePath = sprintf('%s/%s', $this->logoPath, $provider->getLogoName());
                $this->pdf->Image($imagePath, $x + ($i * 25), $y + 0.5, 19, 24, '', '', '', 2, 300, '', false, false, 0, 'CT');
                $i++;
            }

            $this->pdf->SetY($y + 25);
        }

        if ($withoutLogo) {
            $this->pdf->MultiCell(0, 0, implode(', ', array_map(function($provider) {return $provider->getName();}, $withoutLogo)), 0, 'L');
        }
    }

    private function getProvidersLine()
    {
        $names = array();
        foreach ($this->getData() as $record) {
            $names[$record['record']->getProvider()->getId()] = $record['record']->getProvider()->getName();
        }

        return implode(', ', $names);
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