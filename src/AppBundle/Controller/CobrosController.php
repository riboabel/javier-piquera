<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Reserva;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\ReservaLog;
use AppBundle\Form\Type\CobroFilterFormType;

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
     * @Method({"GET"})
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $acts = $manager->createQuery('SELECT c FROM AppBundle:ChargeAct AS c ORDER BY c.createdAt');

        $form = $this->createForm(CobroFilterFormType::class, array('cobradoAt' => 'no-cobrado'));

        return $this->render('App/Cobros/index.html.twig', array(
            'charges' => $acts,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/get-data")
     * @Method({"GET"})
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
        $qb->where($andX);

        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        $form = $this->createForm(CobroFilterFormType::class);
        $form->submit($request->query->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')
                ->addFilterConditions($form, $qb);

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'serviceType') {
                    return 'st.name';
                } elseif ($name == 'startAt') {
                    return 'r.startAt';
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
        $data = array_map(function(Reserva $record) use($getPrice, $template) {
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
     * @Method({"POST"})
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
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $ids = $request->get('ids');
        $prices = $request->get('prices');

        $query = $manager->createQuery('SELECT r FROM AppBundle:Reserva r JOIN r.serviceType st WHERE r.id IN (:ids) ORDER BY r.startAt ASC')
            ->setParameter('ids', $request->get('ids'));
        $records = $query->getResult();

        $act = new \AppBundle\Entity\ChargeAct();
        $manager->persist($act);

        foreach ($records as $record) {
            $record
                ->setChargeAct($act)
                ->setClientPriceAmount(str_replace(',', '.', $prices[array_search($record->getId(), $ids)]))
                ->setCobradoAt(new \DateTime('now'))
                ->addLog(new ReservaLog())
                ;
        }

        $manager->flush();

        return $this->redirect($this->generateUrl('app_cobros_index'));
    }

    /**
     * @Route("/imprimir-cobro")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function printAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $em->createQuery('SELECT r.id FROM AppBundle:Reserva AS r WHERE r.chargeAct = :act ORDER BY r.cobradoAt')
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
