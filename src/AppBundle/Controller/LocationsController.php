<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Location;
use AppBundle\Form\Type\LocationType;

/**
 * Description of LocationsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/localidades")
 */
class LocationsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT l FROM AppBundle:Location l');

        return $this->render('App/Locations/index.html.twig', array(
            'query' => $query
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Location")
     * @param Place $record
     * @return Response
     */
    public function viewAction(Location $record)
    {
        return $this->render('App/Locations/view.html.twig', array(
            'record' => $record
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $record = new Location();
        //$place->setEnterprise($this->getUser()->getEnterprises()[0]);
        $form = $this->createForm(LocationType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($record);
            $em->flush();

            return $this->redirectToRoute('app_locations_index');
        }

        return $this->render('App/Locations/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Location")
     * @param Location $record
     * @return Response
     */
    public function editAction(Location $record, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(LocationType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_locations_index');
        }

        return $this->render('App/Locations/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Location")
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
