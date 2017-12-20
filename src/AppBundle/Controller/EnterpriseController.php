<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\EnterpriseType;
use AppBundle\Entity\Enterprise;

/**
 * Description of EnterpriseController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/empresa")
 */
class EnterpriseController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function viewAction()
    {
        $em = $this->getDoctrine()->getManager();
        $enterprise = $em->getRepository('AppBundle:Enterprise')->findOneBy(array());

        return $this->render('App/Enterprise/view.html.twig', array(
            'enterprise' => $enterprise
        ));
    }

    /**
     * @Route("/editar")
     * @Method({"get", "post"})
     * @param Request $request
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $enterprise = $em->getRepository('AppBundle:Enterprise')->findOneBy(array());
        if (null === $enterprise) {
            $enterprise = new Enterprise('Global enterprise');
        }

        $form = $this->createForm(EnterpriseType::class, $enterprise);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($enterprise);
            $em->flush();

            return $this->redirect($this->generateUrl('app_enterprise_view'));
        }

        return $this->render('App/Enterprise/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
