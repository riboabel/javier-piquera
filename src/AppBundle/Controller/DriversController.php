<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\Driver;
use AppBundle\Form\Type\DriverType;

/**
 * Description of DriversController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/conductores")
 */
class DriversController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('App/Drivers/index.html.twig', array(
            'records' => $em->getRepository('AppBundle:Driver')->findAll()
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     */
    public function viewAction(Driver $driver)
    {
        return $this->render('App/Drivers/view.html.twig', array(
            'record' => $driver
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"get"})
     * @return Response
     */
    public function newAction()
    {
        $driver = new Driver();
        $form = $this->createForm(DriverType::class, $driver);

        return $this->render('App/Drivers/new.html.twig', array(
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
        $form = $this->createForm(DriverType::class, $driver);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($driver);
            $em->flush();

            return $this->redirect($this->generateUrl('app_drivers_index'));
        }

        return $this->render('App/Drivers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     * @param AppBundle\Entity\Driver $driver
     * @return Response
     */
    public function editAction(Driver $driver)
    {
        $form = $this->createForm(DriverType::class, $driver);

        return $this->render('App/Drivers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     * @param AppBundle\Entity\Driver $driver
     * @return array|RedirectResponse
     */
    public function updateAction(Driver $driver, Request $request)
    {
        $form = $this->createForm(DriverType::class, $driver);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('app_drivers_index'));
        }

        return $this->render('App/Drivers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("driver", class="AppBundle\Entity\Driver")
     * @return RedirectResponse
     */
    public function deleteAction(Driver $driver)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($driver);
        $em->flush();

        return $this->redirect($this->generateUrl('app_drivers_index'));
    }
}
