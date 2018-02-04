<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\ReservaTerceroFilterFormType;
use AppBundle\Form\Type\ReservaTerceroFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\ReservaTercero;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ReservasMicrobusController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/reservas-clasicos")
 */
class ReservasClasicosController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $form = $this->createForm(ReservaTerceroFilterFormType::class, array(
            'startAt' => array(
                'left_date' => new \DateTime('now')
            )
        ));

        return $this->render('App/ReservasClasicos/index.html.twig', array(
            'filter' => $form->createView()
        ));
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method({"GET"})
     * @param Request $request
     * @return Response
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:ReservaTercero')
                ->createQueryBuilder('r')
                ->where('r.type = :microbus')
                ->setParameter('microbus', ReservaTercero::TYPE_CLASICOS)
                ;

        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        $form = $this->createForm(ReservaTerceroFilterFormType::class);
        $form->submit($request->query->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'startat') {
                    return 'r.startAt';
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

        $template = $this->container->get('twig')->load('App/ReservasClasicos/_row.html.twig');
        $data = array_map(function(ReservaTercero $record) use($template) {
            return array(
                $template->renderBlock('selector', array('record' => $record)),
                $template->renderBlock('state', array('record' => $record)),
                $template->renderBlock('startAt', array('record' => $record)),
                $template->renderBlock('service', array('record' => $record)),
                $record->getClient()->getName(),
                $record->getClientSerial(),
                $template->renderBlock('provider', array('record' => $record)),
                (string) $record,
                $record->getClientNames(),
                $template->renderBlock('pax', array('record' => $record)),
                $template->renderBlock('actions', array('record' => $record))
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
     * @Route("/nuevo")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $reserva = new ReservaTercero();
        $reserva->setType(ReservaTercero::TYPE_CLASICOS);

        $form = $this->createForm(ReservaTerceroFormType::class, $reserva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($reserva);
            $manager->flush();

            $this->addFlash('notice', 'Registro creado');

            return $this->redirectToRoute('app_reservasclasicos_index');
        }

        return $this->render('App/ReservasClasicos/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param ReservaTercero $reserva
     * @param Request $request
     * @return Response
     */
    public function editAction(ReservaTercero $reserva, Request $request)
    {
        $form = $this->createForm(ReservaTerceroFormType::class, $reserva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($reserva);
            $manager->flush();

            $this->addFlash('notice', 'Registro modificado');

            return $this->redirectToRoute('app_reservasclasicos_index');
        }

        return $this->render('App/ReservasClasicos/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/ejecutar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param ReservaTercero $reserva
     * @param Request $request
     * @return Response
     */
    public function setExecutedAction(ReservaTercero $reserva, Request $request)
    {
        $form = $this->createFormBuilder($reserva)
                ->add('executionIssues')
                ->getForm()
                ;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reserva->setState(ReservaTercero::STATE_EXECUTED);

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return new JsonResponse(array(
                'result' => 'success'
            ));
        }

        return $this->render('App/ReservasClasicos/execute.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/cancelar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param ReservaTercero $reserva
     * @param Request $request
     * @return Response
     */
    public function setCancelledAction(ReservaTercero $reserva, Request $request)
    {
        $form = $this->createFormBuilder($reserva)
                ->add('cancellationIssues')
                ->getForm()
                ;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reserva->setState(ReservaTercero::STATE_CANCELLED);

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return new JsonResponse(array(
                'result' => 'success'
            ));
        }

        return $this->render('App/ReservasClasicos/cancel.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"POST"})
     * @param ReservaTercero $reserva
     * @return Response
     */
    public function deleteAction(ReservaTercero $reserva)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($reserva);
        $manager->flush();

        return new JsonResponse(array('result' => 'success'));
    }

    /**
     * @Route("/get-places", options={"expose": true})
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlacesAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:Place')
            ->createQueryBuilder('p')
            ->select('p.id, p.name')
            ->orderBy('p.name');

        if ($request->get('q')) {
            $qb
                ->where($qb->expr()->like('p.name', ':q'))
                ->setParameter('q', sprintf('%%%s%%', $request->get('q')))
            ;
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb->getQuery(), $request->get('page', 1), 10);

        $total = $pagination->getTotalItemCount();

        $results = array(
            'results' => array_map(function(array $record) {
                return array(
                    'id' => $record['id'],
                    'text' => $record['name']
                );
            }, $pagination->getItems()),
            'pagination' => array(
                'more' => ($request->get('page', 1) * 10) < $total
            )
        );

        return new JsonResponse($results);
    }
}
