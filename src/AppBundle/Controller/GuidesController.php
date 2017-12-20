<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\TravelGuide;
use AppBundle\Form\Type\TravelGuideType;

/**
 * Description of GuidesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/guias")
 */
class GuidesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('App/Guides/index.html.twig');
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

        $qb = $em->getRepository('AppBundle:TravelGuide')
                ->createQueryBuilder('g');

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            $orX->add($qb->expr()->like('g.name', $qb->expr()->literal("%{$search['value']}%")));
            $orX->add($qb->expr()->like('g.contactInfo', $qb->expr()->literal("%{$search['value']}%")));

            $qb->where($orX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'g.name';
                } elseif ($name == 'contactInfo') {
                    return 'g.contactInfo';
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

        $data = array();

        foreach ($pagination->getItems() as $record) {
            $row = array(
                $record->getName(),
                $record->getContactInfo(),
                $this->renderView('App/Guides/index_actions.html.twig', array('record' => $record))
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
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\TravelGuide")
     * @param TravelGuide $record
     * @return Response
     */
    public function viewAction(TravelGuide $record)
    {
        return $this->render('App/Guides/view.html.twig', array('record' => $record));
    }

    /**
     * @Route("/nuevo")
     * @Method({"get"})
     * @return Response
     */
    public function newAction()
    {
        $guide = new TravelGuide();
        $form = $this->createForm(TravelGuideType::class, $guide);

        return $this->render('App/Guides/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/nuevo")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $guide = new TravelGuide();
        $guide->setEnterprise($this->getUser()->getEnterprises()[0]);
        $form = $this->createForm(TravelGuideType::class, $guide);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirectToRoute('app_guides_index');
        }

        return $this->render('App/Guides/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\TravelGuide")
     * @param TravelGuide $record
     * @return Response
     */
    public function editAction(TravelGuide $record)
    {
        $form = $this->createForm(TravelGuideType::class, $record);

        return $this->render('App/Guides/edit.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\TravelGuide")
     * @param AppBundle\Entity\TravelGuide $record
     * @return Response
     */
    public function updateAction(TravelGuide $record, Request $request)
    {
        $form = $this->createForm(TravelGuideType::class, $record);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('app_guides_index');
        }

        return $this->render('App/Guides/edit.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\TravelGuide")
     * @param TravelGuide $record
     * @return JsonResponse
     */
    public function deleteAction(TravelGuide $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }
}
