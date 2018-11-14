<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 11/4/2018
 * Time: 9:38 PM
 */

namespace AppBundle\Lib\Reports;

use AppBundle\Entity\ReservaTercero;
use Doctrine\ORM\EntityManager;

class ThirdProviderPayPreReport extends Report
{
    private $manager;

    /**
     * @var ReservaTercero[]
     */
    private $services;

    /**
     * ThirdProviderPayPreReport constructor.
     * @param array $requestRecords
     * @param EntityManager $manager
     */
    public function __construct(array $requestRecords, EntityManager $manager)
    {
        parent::__construct('P', 'LETTER');

        $this->manager = $manager;
        $this->loadRecords($requestRecords);
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->pdf->SetFontSize(10);

        $this->renderHeader();
        $this->renderBody();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->Cell(20, 0, 'Fecha', 0, 0, 'R');
        $this->pdf->Cell(0, 0, $this->getPayDate()->format('d/m/Y H:i'), 0, 1, 'L');
        $this->pdf->Cell(20, 0, 'Beneficiario', 0, 0, 'R');
        $this->pdf->Cell(0, 0, $this->getPayProvider()->getName(), 0, 1, 'L');
        $this->pdf->ln(4);
    }

    private function renderBody()
    {
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(32, 0, 'Fecha', 1, 0, 'C');
        $this->pdf->Cell(100, 0, 'Servicio', 1, 0, 'C');
        $this->pdf->Cell(40, 0, 'Referencia', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Importe', 1, 1, 'C');

        $this->pdf->SetFont('', '');
        $totalPrice = 0;

        foreach ($this->services as $service) {
            $this->pdf->Cell(32, 0, $service['record']->getStartAt()->format('d/m/Y H:i'), 1, 0);
            $this->pdf->Cell(100, 0, (string) $service['record']->getServiceType(), 1, 0);
            $this->pdf->Cell(40, 0, (string) $service['record']->getClientSerial(), 1, 0);
            $this->pdf->Cell(0, 0, sprintf('%0.2f', $service['charge']), 1, 1, 'R');

            $totalPrice += $service['charge'];

            if ($service['note']) {
                $fontSize = $this->pdf->getFontSizePt();
                $this->pdf->SetFontSize(8);
                $this->pdf->Cell(0, 0, $service['note'], 1, 1, 'J');
                $this->pdf->SetFontSize($fontSize);
            }
        }

        $this->pdf->Cell(172, 0, 'Total', 1, 0);
        $this->pdf->Cell(0, 0, sprintf('%0.2f', $totalPrice), 1, 1, 'R');
    }

    private function loadRecords($requestParams)
    {
        $services = [];

        $query = $this->getQuery($requestParams['ids']);
        $persistedRecords = $query->getResult();

        foreach ($requestParams['ids'] as $index => $id) {
            foreach ($persistedRecords as $record) {
                if ($record->getId() == $id) {
                    $services[] = array(
                        'record' => $record,
                        'note' => $requestParams['notes'][$index],
                        'charge' => $requestParams['charges'][$index]
                    );
                }
            }
        }

        $this->services = $services;
    }

    private function getQuery($ids)
    {
        $queryBuilder = $this->manager->getRepository('AppBundle:ReservaTercero')
            ->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $queryBuilder->getQuery();
    }

    private function getPayDate()
    {
        return new \DateTime('now');
    }

    private function getPayProvider()
    {
        return $this->services[0]['record']->getProvider();
    }
}