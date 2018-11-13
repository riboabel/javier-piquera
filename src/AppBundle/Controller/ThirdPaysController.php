<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ReservaTercero;
use AppBundle\Entity\ThirdPayAct;
use AppBundle\Form\Type\ThirdPayActFormType;
use AppBundle\Form\Type\ThirdPayFilterFormType;
use AppBundle\Lib\Reports\ThirdProviderPayPreReport;
use AppBundle\Lib\Reports\ThirdProviderPayReport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ThridPaysController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/pagos-a-terceros")
 */
class ThirdPaysController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $form = $this->createForm(ThirdPayFilterFormType::class);

        return $this->render('@App/ThirdPays/index.html.twig', array(
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
            ->addSelect('s')
            ->addSelect('p')
            ->addSelect('c')
            ->addSelect('(SELECT pr.payableCharge FROM AppBundle:Price AS pr WHERE pr.provider = c.id AND pr.serviceType = s.id) AS payableCharge')
            ->join('r.provider', 'p')
            ->join('r.serviceType', 's')
            ->join('r.client', 'c')
            ->where('r.state <> :state')
            ->setParameter('state', ReservaTercero::STATE_CANCELLED);

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter['q'] = $search['value'];

        $form = $this->createForm(ThirdPayFilterFormType::class);
        $form->submit($request->query->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);

        if ($orders) {
            $column = call_user_func(function ($name) use ($qb) {
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

        $template = $this->get('twig')->load('@App/ThirdPays/_cells.html.twig');
        $data = array_map(function (array $record) use ($template) {
            return array(
                $template->renderBlock('selector', array('record' => $record[0])),
                $template->renderBlock('service', array('record' => $record[0])),
                $template->renderBlock('startAt', array('record' => $record[0])),
                $template->renderBlock('provider', array('record' => $record[0])),
                $template->renderBlock('provider_reference', array('record' => $record[0])),
                $template->renderBlock('customer', array('record' => $record[0])),
                $template->renderBlock('customer_reference', array('record' => $record[0])),
                $template->renderBlock('state', array('record' => $record[0])),
                $template->renderBlock('charge', array(
                    'current_charge' => $record[0]->getPaidCharge(),
                    'price' => $record['payableCharge']
                ))
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
     * @Route("/pagos")
     * @Method("GET")
     * @return Response
     */
    public function payIndexAction()
    {
        return $this->render('@App/ThirdPays/pay_index.html.twig');
    }

    /**
     * @Route("/obtener-pagos-datos", options={"expose": true})
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPayDataAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $queryBuilder = $manager->getRepository('AppBundle:ThirdPayAct')
            ->createQueryBuilder('p')
            ->addSelect('(SELECT SUM(r.paidCharge) FROM AppBundle:ReservaTercero AS r WHERE r.payAct = p.id) AS price');

        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($orders) {
            $column = call_user_func(function ($name) use ($queryBuilder) {
                if ($name === 'date') {
                    return 'p.createdAt';
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

        $template = $this->container->get('twig')->load('@App/ThirdPays/_pay_row.html.twig');
        $data = array_map(function (array $record) use ($template) {
            return array(
                $record[0]->getCreatedAt()->format('d/m/Y H:i'),
                sprintf('%0.2f', $record['price']),
                $template->renderBlock('actions', array('record' => $record[0]))
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
     * @Route("/prepare-pay", options={"expose": true})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function payAction(Request $request)
    {
        $ids = $request->query->get('id', array());
        if (!$ids) {
            throw $this->createNotFoundException('No service ids');
        }

        $manager = $this->getDoctrine()->getManager();
        $services = $manager
            ->createQuery('SELECT s FROM AppBundle:ReservaTercero AS s WHERE s.id IN (:ids) ORDER BY s.startAt')
            ->setParameter('ids', $ids)
            ->getResult();
        $pay = new ThirdPayAct();
        foreach ($services as $service) {
            $pay->addService($service);
            $price = $this->possibleChargeForServiceAction($service)->getContent();
            $service->setPaidCharge($price);
        }

        $form = $this->createForm(ThirdPayActFormType::class, $pay);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager->persist($pay);
                $manager->flush();

                $this->addFlash('notice', 'Pago creado correctamente');

                return new Response('<script type="text/javascript">$(document).ready(function(){location.href=location.href;});</script>');
            } else {
                return $this->render('@App/ThirdPays/pay_form.html.twig', array(
                    'form' => $form->createView()
                ));
            }
        }

        return $this->render('@App/ThirdPays/prepare_pay.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/generar-pre-reporte-pago", options={"expose": true})
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function printPreReportAction(Request $request)
    {
        $records = array(
            'ids' => $request->request->get('ids'),
            'charges' => $request->request->get('charges'),
            'notes' => $request->request->get('notes')
        );

        $report = new ThirdProviderPayPreReport($records, $this->getDoctrine()->getManager());

        return new StreamedResponse(function () use ($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="third-pay-model.pdf"'
        ));
    }

    /**
     * @Route("/pagados/{id}/imprimir", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param ThirdPayAct $act
     * @return StreamedResponse
     */
    public function printReportAction(ThirdPayAct $act)
    {
        $report = new ThirdProviderPayReport($act);

        return new StreamedResponse(function () use ($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="third-pay-model.pdf"'
        ));
    }

    /**
     * @param ReservaTercero $service
     * @return Response
     */
    public function possibleChargeForServiceAction(ReservaTercero $service)
    {
        $manager = $this->getDoctrine()->getManager();
        $price = $manager->getRepository('AppBundle:Price')
            ->findOneBy(array(
                'provider' => $service->getClient()->getId(),
                'serviceType' => $service->getServiceType()->getId()
            ))
        ;

        if ($price && $price->getPayableCharge()) {
            return new Response($price->getPayableCharge());
        }

        return new Response($service->getServiceType()->getDefaultPayAmount());
    }

    /**
     * @Route("/pagados/{id}/ver")
     * @Method("GET")
     * @param ThirdPayAct $pay
     * @return Response
     */
    public function viewPayAction(ThirdPayAct $pay)
    {
        return $this->render('@App/ThirdPays/view.html.twig', array('record' => $pay));
    }
}