<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Reserva;
use AppBundle\Entity\ReservaLog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of DriverPlanningController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("plnificar-conductores")
 */
class DriverPlanningController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $today = new \DateTime('now');
        $next7days = new \DateTime('+7 days');

        return $this->render('App/DriverPlanning/index.html.twig', array(
            'today' => $today,
            'next_7_days' => $next7days
        ));
    }

    /**
     * @Route("/get-data")
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->join('r.serviceType', 'st')
                ->join('r.provider', 'p')
                ->leftJoin('r.driver', 'd');

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());

        $andX = $qb->expr()->andX(
            $qb->expr()->eq('r.isExecuted', $qb->expr()->literal(false)),
            $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false))
        );

        if (isset($filter['fromDate']) && $filter['fromDate']) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal(date_create_from_format('d/m/Y', $filter['fromDate'])->format('Y-m-d 00:00:00'))));
        }
        if (isset($filter['toDate']) && $filter['toDate']) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal(date_create_from_format('d/m/Y', $filter['toDate'])->format('Y-m-d 23:59:59'))));
        }

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            $matches = array();

            if (1 === preg_match('/^(T|t)(?P<year>\d{1})(?P<month>\d{2})(?P<day>\d{2})(|-(?P<id>\d{2}))$/', $search['value'], $matches)) {
                $date = new \DateTime(sprintf('%s-%s-%s', substr(date('y'), 0, 1).$matches['year'], $matches['month'], $matches['day']));
                $andX = $qb->expr()->andX(
                    $qb->expr()->gte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 00:00:00'))),
                    $qb->expr()->lte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 23:59:59')))
                );
                if (isset($matches['id'])) {
                    $andX->add($qb->expr()->like('r.id', $qb->expr()->literal("%{$matches['id']}")));
                }
                $orX->add($andX);
            } else {
                $orX->add($qb->expr()->like('r.providerReference', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('r.serviceDescription', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('st.name', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('p.name', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('d.name', $qb->expr()->literal("%{$search['value']}%")));
            }

            if ($orX->count() > 0) {
                $andX->add($orX);
            }
        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'serialNumber' || $name == 'startAt') {
                    return 'r.startAt';
                } elseif ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'providerReference') {
                    return 'r.providerReference';
                } elseif ($name == 'serviceType') {
                    return 'st.name';
                } elseif ($name == 'serviceDescription') {
                    return 'r.serviceDescription';
                } elseif ($name == 'driver') {
                    return 'd.name';
                } elseif ($name == 'endAt') {
                    return 'r.endAt';
                }
                return null;
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        if ($request->get('length')) {
            $paginator = $this->get('knp_paginator');
            $page = $request->get('start', 0) / $request->get('length') + 1;
            $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

            $list = $pagination->getItems();
            $total = $pagination->getTotalItemCount();
        } else {
            $list = $qb->getQuery()->getResult();
            $total = count($list);
        }

        $data = array();

        foreach ($list as $record) {
            /** @var Reserva $record */
            $row = array(
                $record->getSerialNumber(),
                $record->getProvider()->getName(),
                $record->getProviderReference(),
                $record->getServiceType()->getName(),
                $record->getPlainServiceDescription(),
                $record->getStartAt()->format('d/m/Y H:i'),
                $record->getEndAt() ? $record->getEndAt()->format('d/m/Y H:i') : '',
                sprintf('<select class="form-control input-sm" data-save-url="%s"><option value="%s" selected="selected">%s</option></select>',
                    $this->generateUrl('app_driverplanning_savedriver', array('id' => $record->getId())),
                    $record->getDriver() ? $record->getDriver()->getId() : '',
                    $record->getDriver() ? (string)$record->getDriver() : ''
                )
            );

            $data[] = $row;
        }

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/get-drivers")
     * @Method({"get"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriversAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AppBundle:Driver')
                ->createQueryBuilder('d')
                ->orderBy('d.name');

        if ($request->get('q')) {
            $qb->where($qb->expr()->like('d.name', $qb->expr()->literal("%{$request->get('q')}%")));
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb->getQuery(), $request->get('page', 1), 10);

        $total = $pagination->getTotalItemCount();

        $results = array(
            'results' => array(),
            'pagination' => array(
                'more' => ($request->get('page', 1) * 10) < $total
            )
        );

        foreach ($pagination as $record) {
            $results['results'][] = array(
                'id' => $record->getId(),
                'text' => $record->getName()
            );
        }

        return new JsonResponse($results);
    }

    /**
     * @Route("/{id}/set-driver", requirements={"id": "\d+"})
     * @ParamConverter("reserva", class="AppBundle\Entity\Reserva")
     * @Method({"post"})
     * @param \AppBundle\Entity\Reserva
     * @param Request
     * @return JsonResponse
     */
    public function saveDriverAction(\AppBundle\Entity\Reserva $reserva, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $reserva->setDriver($manager->find('AppBundle:Driver', $request->get('driver')));
        $reserva->addLog(new ReservaLog());

        $manager->flush();

        return new JsonResponse(array('result' => 'success'));
    }
}
