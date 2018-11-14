<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ReservaTercero;
use AppBundle\Entity\ThirdCobro;
use AppBundle\Form\Type\ThirdCobroActFormType;
use AppBundle\Form\Type\ThirdCobroFilterFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ThirdCobrosController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/cobros-a-terceros")
 */
class ThirdCobrosController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $form = $this->createForm(ThirdCobroFilterFormType::class, array(
            'cobrado' => 'no'
        ));

        return $this->render('@App/ThirdCobros/index.html.twig', array(
            'filter' => $form->createView()
        ));
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $qb = $manager->getRepository('AppBundle:ReservaTercero')
            ->createQueryBuilder('r')
            ->where('r.state <> :state')
            ->setParameter('state', ReservaTercero::STATE_CANCELLED)
        ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter['q'] = $search['value'];

        $form = $this->createForm(ThirdCobroFilterFormType::class);
        $form->submit($request->query->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);

        if ($orders) {
            $column = call_user_func(function($name) use($qb) {
                if ($name === 'startat') {
                    return 'r.startAt';
                } elseif ($name === 'service') {
                    $qb->join('r.serviceType', 's');

                    return 's.name';
                }

                return null;
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));
        $total = $pagination->getTotalItemCount();

        $template = $this->get('twig')->load('@App/ThirdCobros/_cells.html.twig');
        $data = array_map(function(ReservaTercero $record) use($template) {
            return array(
                $template->renderBlock('selector', array('record' => $record)),
                $template->renderBlock('service', array('record' => $record)),
                $template->renderBlock('startAt', array('record' => $record)),
                $template->renderBlock('provider', array('record' => $record)),
                $template->renderBlock('provider_reference', array('record' => $record)),
                $template->renderBlock('customer', array('record' => $record)),
                $template->renderBlock('customer_reference', array('record' => $record)),
                $template->renderBlock('state', array('record' => $record)),
                $template->renderBlock('charge', array('record' => $record))
            );
        }, $pagination->getItems());

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/cobrados")
     * @Method("GET")
     * @return Response
     */
    public function indexCobrosAction()
    {
        return $this->render('AppBundle:ThirdCobros:index_cobros.html.twig');
    }

    /**
     * @Route("/cobrados/obtener-datos", options={"expose": true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCobrosDataAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $queryBuilder = $manager->getRepository('AppBundle:ThirdCobro')
            ->createQueryBuilder('c')
            ->select('c AS record', '(SELECT SUM(s.cobroCharge) FROM AppBundle:ReservaTercero AS s WHERE s.cobro = c.id) AS totalPrice')
        ;
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($orders) {
            $column = call_user_func(function($name) use($queryBuilder) {
                if ($name === 'created_at') {
                    return 'c.createdAt';
                }

                return null;
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $queryBuilder->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($queryBuilder->getQuery(), $page, $request->get('length'));
        $total = $pagination->getTotalItemCount();

        $template = $this->container->get('twig')->load('AppBundle:ThirdCobros:_cells_cobros.html.twig');
        $data = array_map(function($record) use ($template) {
            $services = $record['record']->getServices();

            return array(
                $record['record']->getCreatedAt()->format('d/m/Y H:i'),
                $services[0]->getClient()->getName(),
                sprintf('%0.2f', $record['totalPrice']),
                $template->renderBlock('actions', array('record' => $record['record']))
            );
        }, $pagination->getItems());

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    public function getPossibleChargeForServiceAction(ReservaTercero $service)
    {
        $manager = $this->getDoctrine()->getManager();
        $price = $manager->getRepository('AppBundle:Price')->findOneBy(array(
            'serviceType' => $service->getServiceType()->getId(),
            'provider' => $service->getClient()->getId()
        ));

        if ($price && $price->getReceivableCharge()) {
            return new Response($price->getReceivableCharge());
        }

        return new Response($service->getServiceType()->getDefaultPrice());
    }

    /**
     * @Route("/cobrar", options={"expose": true})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function cobrarAction(Request $request)
    {
        $ids = $request->query->get('id');
        if (!$ids) {
            throw $this->createNotFoundException('No ids');
        }

        $manager = $this->getDoctrine()->getManager();
        $services = $manager
            ->createQuery('SELECT s FROM AppBundle:ReservaTercero AS s WHERE s.id in (:ids) AND s.cobro IS NULL ORDER BY s.startAt')
            ->setParameter('ids', $ids)
            ->getResult()
            ;
        $cobro = new ThirdCobro();
        foreach ($services as $service) {
            $cobro->addService($service);
            $charge = $this->getPossibleChargeForServiceAction($service)->getContent();
            $service->setCobroCharge($charge);
        }

        $form = $this->createForm(ThirdCobroActFormType::class, $cobro);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager->persist($cobro);
                $manager->flush();

                $this->addFlash('notice', 'Cobro realizado');

                return new Response('<script type="text/javascript">$(document).ready(function() {location.href=location.href;});</script>');
            } else {
                return $this->render('@App/ThirdCobros/cobro_form.html.twig', array(
                    'form' => $form->createView()
                ));
            }
        }

        return $this->render('@App/ThirdCobros/prepare_cobro.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/cobrados/{id}/ver", requirements={"id": "\d+"})
     * @Method("GET")
     * @param ThirdCobro $record
     * @return Response
     */
    public function viewAction(ThirdCobro $record)
    {
        return $this->render('@App/ThirdCobros/view.html.twig', array('record' => $record));
    }

    /**
     * @Route("/cobrados/generar-pre-modelo", options={"expose": true})
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function printPreReportAction(Request $request)
    {

    }

    /**
     * @Route("/cobrados/{id}/imprimir-model", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param ThirdCobro $record
     * @return StreamedResponse
     */
    public function printReportAction(ThirdCobro $record)
    {

    }
}
