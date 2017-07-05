<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\Provider;
use AppBundle\Form\Type\ProviderType;

/**
 * Description of ProvidersController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/agencias")
 */
class ProvidersController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('App/Providers/index.html.twig', array(
            'records' => $em->getRepository('AppBundle:Provider')->findAll()
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Provider")
     * @param Provider $record
     * @return Response
     */
    public function viewAction(Provider $record)
    {
        return $this->render('App/Providers/view.html.twig', array(
            'record' => $record
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"get"})
     * @return Response
     */
    public function newAction()
    {
        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider);

        return $this->render('App/Providers/new.html.twig', array(
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
        $provider = new Provider();
        $provider->setEnterprise($this->getUser()->getEnterprises()[0]);
        $form = $this->createForm(ProviderType::class, $provider);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirectToRoute('app_providers_index');
        }

        return $this->render('App/Providers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Provider")
     * @param Provider $record
     * @return Response
     */
    public function editAction(Provider $record)
    {
        $form = $this->createForm(ProviderType::class, $record);

        return $this->render('App/Providers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Provider")
     * @param Provider $record
     * @return Response
     */
    public function updateAction(Provider $record, Request $request)
    {
        $form = $this->createForm(ProviderType::class, $record);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('app_providers_index');
        }

        return $this->render('App/Providers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Provider")
     * @param Provider $record
     * @return RedirectResponse
     */
    public function deleteAction(Provider $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return $this->redirectToRoute('app_providers_index');
    }
}
