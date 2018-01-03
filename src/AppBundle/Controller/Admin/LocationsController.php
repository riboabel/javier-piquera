<?php

namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Location;
use AppBundle\Form\Type\LocationFormType;

/**
 * Description of LocationsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/localidades")
 */
class LocationsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Ressponse
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT l FROM AppBundle:Location l');

        return $this->render('@App/Admin/Locations/index.html.twig', array(
            'query' => $query
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param Location $record
     * @return Response
     */
    public function viewAction(Location $record)
    {
        return $this->render('@App/Admin/Locations/view.html.twig', array(
            'record' => $record
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
        $record = new Location();
        $form = $this->createForm(LocationFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $manager->persist($record);
            $manager->flush();

            return $this->redirectToRoute('app_admin_locations_index');
        }

        return $this->render('@App/Admin/Locations/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param Location $record
     * @param Request $request
     * @return Response
     */
    public function editAction(Location $record, Request $request)
    {
        $form = $this->createForm(LocationFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return $this->redirectToRoute('app_admin_locations_index');
        }

        return $this->render('@App/Admin/Locations/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @param Location $record
     * @return JsonResponse
     */
    public function deleteAction(Location $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }
}
