<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;

/**
 * Description of UsersController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/usuarios")
 */
class UsersController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('App/Users/index.html.twig', array(
            'records' => $em->getRepository('AppBundle:User')->findAll()
        ));
    }

    /**
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @return Response
     */
    public function viewAction(User $record)
    {
        return $this->render('App/Users/view.html.twig', array(
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
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $form = $this->createForm(UserType::class, $user);

        return $this->render('App/Users/new.html.twig', array(
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
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            $user->addEnterprise($this->getUser()->getEnterprises()[0]);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $userManager->updateUser($form->getData());

            return $this->redirectToRoute('app_users_index');
        }

        return $this->render('App/Users/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @param User $record
     * @return Response
     */
    public function editAction(User $record)
    {
        $form = $this->createForm(UserType::class, $record);

        return $this->render('App/Users/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function updateAction(User $record, Request $request)
    {
        $form = $this->createForm(UserType::class, $record);
        $userManager = $this->container->get('fos_user.user_manager');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $userManager->updateUser($record);

            return $this->redirectToRoute('app_users_index');
        }

        return $this->render('App/Users/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @param User $user
     * @return RedirectResponse
     */
    public function deleteAction(User $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return $this->redirectToRoute('app_users_index');
    }
}
