<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Reserva;

/**
 * ReservaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReservaRepository extends EntityRepository
{
    /**
     * @return int
     */
    public function countAll()
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT(r) FROM AppBundle:Reserva r WHERE r.isCancelled = false');

        $result = $query->getResult();

        return $result[0][1];
    }

    /**
     * @param \DateTime $date
     * @return int
     */
    public function countNotReadyReservasForDate(\DateTime $date)
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT(r) FROM AppBundle:Reserva r WHERE r.isCancelled = false AND (r.startAt >= :dateStart AND r.startAt <= :dateEnd) AND (r.driver IS NULL OR r.isDriverConfirmed = false)')
                ->setParameters(array(
                    'dateStart' => $date->format('Y-m-d 00:00:00'),
                    'dateEnd' => $date->format('Y-m-d 23:59:59')
                ));

        $result = $query->getResult();

        return $result[0][1];
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    public function countReadyBetweenDates(\DateTime $start = null, \DateTime $end = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->getRepository($this->getEntityName())
                ->createQueryBuilder('r')
                ->select('COUNT(r)');

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false)),
                $qb->expr()->isNotNull('r.driver'),
                $qb->expr()->eq('r.isDriverConfirmed', $qb->expr()->literal(true)),
                $qb->expr()->eq('r.isExecuted', $qb->expr()->literal(false))
        );

        if ($start) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($start->format('Y-m-d 00:00:00'))));
        }
        if ($end) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($end->format('Y-m-d 23:59:59'))));
        }

        $qb->where($andX);

        $result = $qb->getQuery()->getResult();

        return $result[0][1];
    }

    public function findBySerialNumber($serialNumber)
    {
        $qb = $this->createQueryBuilder('r');
        $andX = $qb->expr()->andX();

        $matches = array();
        preg_match('/^T(?P<year>\d{1})(?P<month>\d{2})(?P<day>\d{2})-(?P<id>(\d{2}|\d{4}))$/i', $serialNumber, $matches);

        $date = new \DateTime(sprintf('%s-%s-%s', substr(date('y'), 0, 1).$matches['year'], $matches['month'], $matches['day']));
        $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 00:00:00'))));
        $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 23:59:59'))));
        $andX->add($qb->expr()->like('r.id', $qb->expr()->literal(sprintf('%%%s', ltrim($matches['id'], '0')))));
        if (2 === strlen($matches['id'])) {
            $andX->add($qb->expr()->lte('r.id', $qb->expr()->literal(2493)));
        }

        return $qb->where($andX)->getQuery()->getSingleResult();
    }
}
