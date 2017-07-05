<?php

namespace AppBundle\Lib\Reports;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Provider;

class PricesByProvider extends Report
{
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
    private $manager;


    public function __construct(EntityManager $manager, Provider $provider = null, array $services)
    {
        parent::__construct('L', 'LETTER');

        $this->manager = $manager;
        $this->provider = $provider;
        $this->services = $services;
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->renderBody();

        return $this->getPdfContent();
    }

    private function renderBody()
    {
        $this->pdf->SetFont('', 'B', 14);

        if (null !== $this->provider) {
            $this->pdf->Cell(0, 0, sprintf('Precios para %s', (string) $this->provider), 1, 1, 'C');
        } else {
            $this->pdf->Cell(0, 0, 'Precios predefinidos', 1, 1, 'C');
        }

        $this->pdf->Ln(3);

        $this->pdf->SetFont('', 'B', 10);
        $this->pdf->Cell(220, 0, 'Servicio', 1, 0);
        $this->pdf->Cell(0, 0, 'Importe', 1, 1, 'R');

        $this->pdf->SetFont('', '', 10);

        foreach ($this->getServicesQuery()->getResult() as $service) {
            $this->pdf->Cell(220, 0, $service['name'], 1, 0);
            $this->pdf->Cell(0, 0, sprintf('%0.2f', isset($service['receivableCharge']) && null !== $service['receivableCharge'] ? $service['receivableCharge'] : $service['defaultPrice']), 1, 1, 'R');
        }
    }

    private function getServicesQuery()
    {
        $qb = $this->manager->getRepository('AppBundle:ServiceType')
            ->createQueryBuilder('s')
            ->select('s.name, s.defaultPrice')
            ->orderBy('s.name')
            ;

        if (null !== $this->provider) {
            $qb->add('select', '(SELECT pr.receivableCharge FROM AppBundle:Price pr JOIN pr.provider p JOIN pr.serviceType st WHERE st.id = s.id AND p.id = :provider) AS receivableCharge', true)
                    ->setParameter('provider', $this->provider->getId());
        }

        if ($this->services) {
            $qb->where($qb->expr()->in('s.id', array_map(function($element) {return $element->getId();}, $this->services)));
        }

        return $qb->getQuery();
    }
}
