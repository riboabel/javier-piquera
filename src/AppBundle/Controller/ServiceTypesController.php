<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\ServiceType;
use AppBundle\Form\Type\ServiceTypeType;

/**
 * Description of ServiceTypesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/servicios")
 */
class ServiceTypesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('App/ServiceTypes/index.html.twig', array(
            'records' => $em->getRepository('AppBundle:ServiceType')->findAll()
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\ServiceType")
     * @param ServiceType $record
     * @return Response
     */
    public function viewAction(ServiceType $record)
    {
        return $this->render('App/ServiceTypes/view.html.twig', array('record' => $record));
    }

    /**
     * @Route("/nuevo")
     * @Method({"get"})
     * @return Response
     */
    public function newAction()
    {
        $serviceType = new ServiceType();
        $form = $this->createForm(ServiceTypeType::class, $serviceType);

        return $this->render('App/ServiceTypes/new.html.twig', array(
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
        $serviceType = new ServiceType();
        $serviceType->setEnterprise($this->getUser()->getEnterprises()[0]);
        $form = $this->createForm(ServiceTypeType::class, $serviceType);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('notice', 'Registro creado');

            return $this->redirectToRoute('app_servicetypes_index');
        }

        return $this->render('App/ServiceTypes/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\ServiceType")
     * @param ServiceType $record
     * @return array
     */
    public function editAction(ServiceType $record)
    {
        $form = $this->createForm(ServiceTypeType::class, $record);

        return $this->render('App/ServiceTypes/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\ServiceType")
     * @param ServiceType $record
     * @return Response
     */
    public function updateAction(ServiceType $record, Request $request)
    {
        $form = $this->createForm(ServiceTypeType::class, $record);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('notice', 'Registro modificado');

            return $this->redirect($this->generateUrl('app_servicetypes_index'));
        }

        return $this->render('App/ServiceTypes/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\ServiceType")
     * @param ServiceType $record
     * @return RedirectResponse
     */
    public function deleteAction(ServiceType $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return $this->redirectToRoute('app_servicetypes_index');
    }
}
