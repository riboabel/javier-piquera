<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;

/**
 * Description of MorrisGraphGenerator
 *
 * @author Raibel Botta
 */
class MorrisGraphGenerator
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getData()
    {
        $start = date_create('now')->sub(new \DateInterval('P11M'));
        $end = date_create('now')->add(new \DateInterval('P1M'));

        $query = $this->createQuery()->setParameters(array(
            'start' => $start->format('Y-m-d 00:00:00'),
            'end' => $end->format('Y-m-d 23:59:59')
        ));

        $results = array();

        foreach ($query->getResult() as $record) {
            if (!isset($results[$record['startAt']->format('Y-m')][$record['id']])) {
                $results[$record['startAt']->format('Y-m')][$record['id']] = 0;
            }
            $results[$record['startAt']->format('Y-m')][$record['id']]++;
        }

        $services = array();
        foreach ($this->getServices() as $s) {
            $services['s'.$s['id']] = $s['name'];
        }

        $data = array(
            'data' => array(),
            'xkey' => 'period',
            'ykeys' => array_keys($services),
            'labels'=> array_values($services)
        );

        foreach ($results as $date => $result) {
            $line = array(
                'period' => $date
            );
            foreach ($this->getServices() as $service) {
                $line['s'.$service['id']] = isset($result[$service['id']]) ? $result[$service['id']] : 0;
            }

            $data['data'][] = $line;
        }

        return $data;
    }

    private function createQuery()
    {
        $qb = $this->em->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->join('r.serviceType', 's')
                ->select('r.startAt, s.id, s.name');

        $qb->where($qb->expr()->andX(
            $qb->expr()->gte('r.startAt', ':start'),
            $qb->expr()->lte('r.startAt', ':end'),
            $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false)),
            $qb->expr()->eq('r.isExecuted', $qb->expr()->literal(true))
        ));

        return $qb->getQuery();
    }

    private function getServices()
    {
        return $this->em->createQuery('SELECT s.id, s.name FROM AppBundle:ServiceType s ORDER BY s.name')->getResult();
    }
}
