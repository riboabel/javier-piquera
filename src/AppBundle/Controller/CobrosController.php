<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\ReservaLog;

/**
 * Description of CobrosController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/cobros")
 */
class CobrosController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $acts = $em->createQuery('SELECT c FROM AppBundle:ChargeAct c ORDER BY c.createdAt');
        $providers = $em->createQuery('SELECT p FROM AppBundle:Provider p ORDER BY p.name');

        return $this->render('App/Cobros/index.html.twig', array(
            'providers' => $providers,
            'charges' => $acts
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
                ;

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('p.receiveInvoice', $qb->expr()->literal(false)),
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false))
        );

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());

        if (isset($filter['from']) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $filter['from'])) {
            $from = date_create_from_format('d/m/Y', $filter['from']);
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($from->format('Y-m-d 00:00:00'))));
        }

        if (isset($filter['to']) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $filter['to'])) {
            $to = date_create_from_format('d/m/Y', $filter['to']);
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($to->format('Y-m-d 23:59:59'))));
        }

        if (isset($filter['provider']) && $filter['provider']) {
            $andX->add($qb->expr()->eq('p.id', $qb->expr()->literal($filter['provider'])));
        }

        if (isset($filter['state'])) {
            if ($filter['state'] === 'no-cobrado') {
                $andX->add($qb->expr()->isNull('r.cobradoAt'));
            } elseif ($filter['state'] === 'cobrado') {
                $andX->add($qb->expr()->isNotNull('r.cobradoAt'));
            }
        }

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            if (1 === preg_match('/^\d{4}$/', $search['value'])) {
                $orX->add($qb->expr()->andX(
                    $qb->expr()->gte('r.startAt', $qb->expr()->literal(sprintf('%s-01-01 00:00', $search['value']))),
                    $qb->expr()->lte('r.startAt', $qb->expr()->literal(sprintf('%s-12-31 23:59', $search['value'])))
                ));
            } elseif (1 === preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $search['value'])) {
                $date = date_create_from_format('d/m/Y', $search['value']);
                $orX->add($qb->expr()->andX(
                    $qb->expr()->gte('r.startAt', $qb->expr()->literal(sprintf('%s 00:00', $date->format('Y-m-d')))),
                    $qb->expr()->lte('r.startAt', $qb->expr()->literal(sprintf('%s 23:59', $date->format('Y-m-d'))))
                ));
            } else {
                $orX->add($qb->expr()->like('st.name', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('p.name', $qb->expr()->literal("%{$search['value']}%")));
                foreach (explode(' ', $search['value']) as $q) {
                    $orX->add($qb->expr()->like('r.clientNames', $qb->expr()->literal(sprintf('%%%s%%', $q))));
                }
            }


            $andX->add($orX);
        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'serviceType') {
                    return 'st.name';
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

        $getPrice = function(\AppBundle\Entity\Reserva $record) use($em) {
            $price = $em->getRepository('AppBundle:Price')->findOneBy(array(
                'provider' => $record->getProvider()->getId(),
                'serviceType' => $record->getServiceType()->getId()
            ));

            if (null !== $price && null !== $price->getReceivableCharge()) {
                $value = $price->getReceivableCharge();
            } else {
                $value = $record->getServiceType()->getDefaultPrice();
            }

            return $value;
        };

        $template = $this->container->get('twig')->loadTemplate('App/Cobros/_row.html.twig');
        $data = array_map(function($record) use($getPrice, $template) {
            return array(
                $template->renderBlock('select', array('record' => $record)),
                $record->getSerialNumber(),
                $template->renderBlock('start', array('record' => $record)),
                $record->getProvider()->getName(),
                $record->getProviderReference(),
                $record->getClientNames(),
                $template->renderBlock('service', array('record' => $record)),
                $template->renderBlock('amount', array('amount' => $getPrice($record)))
            );
        }, $list);


        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/preparar")
     * @Method({"post"})
     * @param Request $request
     * @return array
     */
    public function prepareAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $query = $manager->createQuery('SELECT r, st, p FROM AppBundle:Reserva r JOIN r.serviceType st JOIN r.provider p WHERE r.id IN (:ids) ORDER BY r.startAt ASC')
            ->setParameter('ids', $request->get('ids'));
        $records = $query->getResult();

        $prices = array();
        foreach ($records as $record) {
            $price = $manager->getRepository('AppBundle:Price')->findOneBy(array(
                'provider' => $record->getProvider()->getId(),
                'serviceType' => $record->getServiceType()->getId()
            ));

            if (null !== $price && null !== $price->getReceivableCharge()) {
                $prices[$record->getId()] = $price->getReceivableCharge();
            } else {
                $prices[$record->getId()] = $record->getServiceType()->getDefaultPrice();
            }
        }

        return $this->render('App/Cobros/prepare.html.twig', array(
            'records' => $records,
            'prices' => $prices
        ));
    }

    /**
     * @Route("/ejecutar")
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $request->get('ids');
        $prices = $request->get('prices');

        $query = $em->createQuery('SELECT r FROM AppBundle:Reserva r JOIN r.serviceType st WHERE r.id IN (:ids) ORDER BY r.startAt ASC')
            ->setParameter('ids', $request->get('ids'));
        $records = $query->getResult();

        $act = new \AppBundle\Entity\ChargeAct();
        $em->persist($act);

        foreach ($records as $record) {
            $record
                ->setChargeAct($act)
                ->setClientPriceAmount(str_replace(',', '.', $prices[array_search($record->getId(), $ids)]))
                ->setCobradoAt(new \DateTime('now'))
                ->addLog(new ReservaLog())
                ;
        }

        $em->flush();

        return $this->redirect($this->generateUrl('app_cobros_index'));
    }

    /**
     * @Route("/imprimir-cobro")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $em->createQuery('SELECT r.id FROM AppBundle:Reserva r WHERE r.chargeAct = :act ORDER BY r.cobradoAt')
            ->setParameter('act', $request->get('id'))
            ->getResult();

        $report = new \AppBundle\Lib\Reports\ChargeForm(array(
            'ids' => array_map(function($id) {
                return $id['id'];
            }, $ids),
            'prices' => array()
        ), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }
}
