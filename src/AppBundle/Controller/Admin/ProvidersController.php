<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\ThirdProvider;
use AppBundle\Form\Type\ThirdProviderFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProvidersController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/proveedores")
 */
class ProvidersController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('App/Admin/Providers/index.html.twig');
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

        $qb = $manager->getRepository('AppBundle:ThirdProvider')
            ->createQueryBuilder('p')
            ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            $orX->add($qb->expr()->like('p.name', ':q'));
            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
            $qb->where($orX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'p.name';
                } elseif ($name == 'type') {
                    return 'p.type';
                } elseif ($name == 'serialPrefix') {
                    return 'p.serialPrefix';
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

        $template = $this->container->get('twig')->load('App/Admin/Providers/_row.html.twig');
        $data = array_map(function(ThirdProvider $provider) use($template) {
            return array(
                $provider->getName(),
                $template->renderBlock('type', array(
                    'record' => $provider
                )),
                $template->renderBlock('serial_prefix', array(
                    'record' => $provider
                )),
                $template->renderBlock('actions', array('record' => $provider))
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
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param ThirdProvider $provider
     * @return Response
     */
    public function viewAction(ThirdProvider $provider)
    {
        return $this->render('App/Admin/Providers/view.html.twig', array(
            'record' => $provider
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
        $provider = new ThirdProvider();

        $form = $this->createForm(ThirdProviderFormType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($provider);
            $manager->flush();

            return $this->redirectToRoute('app_admin_providers_index');
        }

        return $this->render('App/Admin/Providers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param ThirdProvider $provider
     * @param Request $request
     * @return Response
     */
    public function editAction(ThirdProvider $provider, Request $request)
    {
        $form = $this->createForm(ThirdProviderFormType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return $this->redirectToRoute('app_admin_providers_index');
        }

        return $this->render('App/Admin/Providers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method("POST")
     * @param ThirdProvider $provider
     * @return Response
     */
    public function deleteAction(ThirdProvider $provider)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($provider);
        $manager->flush();

        return $this->redirectToRoute('app_admin_providers_index');
    }
}
