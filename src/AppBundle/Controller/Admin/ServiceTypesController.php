<?php

namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\ServiceType;
use AppBundle\Form\Type\ServiceTypeFormType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ServiceTypesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/servicios")
 */
class ServiceTypesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();

        return $this->render('@App/Admin/ServiceTypes/index.html.twig', array(
            'records' => $manager->getRepository('AppBundle:ServiceType')->findAll()
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param ServiceType $record
     * @return Response
     */
    public function viewAction(ServiceType $record)
    {
        return $this->render('@App/Admin/ServiceTypes/view.html.twig', array('record' => $record));
    }

    /**
     * @Route("/nuevo")
     * @Method({"GET"})
     * @return Response
     */
    public function newAction()
    {
        $serviceType = new ServiceType();
        $form = $this->createForm(ServiceTypeFormType::class, $serviceType);

        return $this->render('@App/Admin/ServiceTypes/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $serviceType = new ServiceType();
        $serviceType->setEnterprise($this->getUser()->getEnterprises()[0]);
        $form = $this->createForm(ServiceTypeFormType::class, $serviceType);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('notice', 'Registro creado');

            return $this->redirectToRoute('app_admin_servicetypes_index');
        }

        return $this->render('@App/Admin/ServiceTypes/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param ServiceType $record
     * @return
     */
    public function editAction(ServiceType $record)
    {
        $form = $this->createForm(ServiceTypeFormType::class, $record);

        return $this->render('@App/Admin/ServiceTypes/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"POST"})
     * @param ServiceType $record
     * @param Request $request
     * @return Response
     */
    public function updateAction(ServiceType $record, Request $request)
    {
        $form = $this->createForm(ServiceTypeFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('notice', 'Registro modificado');

            return $this->redirect($this->generateUrl('app_admin_servicetypes_index'));
        }

        return $this->render('@App/Admin/ServiceTypes/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param ServiceType $record
     * @return RedirectResponse
     */
    public function deleteAction(ServiceType $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return $this->redirectToRoute('app_admin_servicetypes_index');
    }
}
