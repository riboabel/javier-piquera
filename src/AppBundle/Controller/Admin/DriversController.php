<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Form\Type\DriverFilterFormType;
use AppBundle\Form\Type\DriverFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\Driver;

/**
 * Description of DriversController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/conductores")
 */
class DriversController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $form = $this->createForm(DriverFilterFormType::class);

        return $this->render('App/Admin/Drivers/index.html.twig', array(
            'filter' => $form->createView()
        ));
    }

    /**
     * @Route("/obtener-datos", options={"expose": true})
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $qb = $manager->getRepository('AppBundle:Driver')
                ->createQueryBuilder('d')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            $orX->add($qb->expr()->like('d.name', ':q'));
            $orX->add($qb->expr()->like('d.contactInfo', ':q'));

            $qb
                ->where($orX)
                ->setParameter('q', sprintf('%%%s%%', $search['value']))
                ;
        }

        $form = $this->createForm(DriverFilterFormType::class);
        $form->submit($request->query->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'd.name';
                } elseif ($name === 'isDriverGuide') {
                    return 'd.isDriverGuide';
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

        $template = $this->container->get('twig')->load('App/Admin/Drivers/_row.html.twig');
        $data = array_map(function(Driver $record) use($template) {
            return array(
                $record->getName(),
                $template->renderBlock('phone', array('phone' => $record->getMobilePhone())),
                $template->renderBlock('phone', array('phone' => $record->getFixedPhone())),
                $template->renderBlock('contact_info', array('record' => $record)),
                $template->renderBlock('is_guide', array('record' => $record)),
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
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     */
    public function viewAction(Driver $driver)
    {
        return $this->render('App/Admin/Drivers/view.html.twig', array(
            'record' => $driver
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"GET"})
     * @return Response
     */
    public function newAction()
    {
        $driver = new Driver();
        $form = $this->createForm(DriverFormType::class, $driver);

        return $this->render('App/Admin/Drivers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $driver = new Driver();
        $driver->setEnterprise($this->getUser()->getEnterprises()[0]);
        $form = $this->createForm(DriverFormType::class, $driver);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($driver);
            $em->flush();

            $this->addFlash('notice', 'Registro creado');

            return $this->redirect($this->generateUrl('app_admin_drivers_index'));
        }

        return $this->render('App/Admin/Drivers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     * @param Driver $driver
     * @return Response
     */
    public function editAction(Driver $driver)
    {
        $form = $this->createForm(DriverFormType::class, $driver);

        return $this->render('App/Admin/Drivers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     * @param \AppBundle\Entity\Driver
     * @return Response
     */
    public function updateAction(Driver $driver, Request $request)
    {
        $form = $this->createForm(DriverFormType::class, $driver);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('notice', 'Registro modificado');

            return $this->redirect($this->generateUrl('app_admin_drivers_index'));
        }

        return $this->render('App/Admin/Drivers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"POST"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     * @param Driver $driver
     * @return RedirectResponse
     */
    public function deleteAction(Driver $driver)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($driver);
        $em->flush();

        if ($this->get('request')->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'result' => 'success'
            ));
        }

        return $this->redirectToRoute('app_drivers_index');
    }
}
