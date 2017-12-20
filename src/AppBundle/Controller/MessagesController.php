<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Message;
use AppBundle\Form\Type\MessageType;

/**
 * Description of MessagesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/mensajes")
 */
class MessagesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('App/Messages/index.html.twig');
    }

    /**
     * @Route("/nuevo")
     * @Method({"get"})
     * @return Response
     */
    public function newAction()
    {
        $message = new Message();
        $message->setWriter($this->getUser());

        $form = $this->createForm(MessageType::class, $message);

        return $this->render('App/Messages/new.html.twig', array(
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
        $message = new Message();
        $message->setWriter($this->getUser());

        $form = $this->createForm(MessageType::class, $message);
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('App/Messages/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/leer", requirements={"id": "\d+"})
     * @param Message $message
     * @return Response
     */
    public function readAction(Message $message)
    {
        $manager = $this->getDoctrine()->getManager();
        $message->setReadedAt(new \DateTime('now'));
        $manager->flush();

        return $this->render('App/Messages/read.html.twig', array(
            'message' => $message
        ));
    }

    /**
     * @return Response
     */
    public function dropdownAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT m FROM AppBundle:Message m WHERE m.reader = :me AND m.readedAt IS NULL ORDER BY m.createdAt')
                ->setMaxResults(5)
                ->setParameters(array(
                    'me' => $this->getUser()->getId()
                ));

        return $this->render('App/Messages/dropdown.html.twig', array(
            'messages' => $query->getResult()
        ));
    }
}
