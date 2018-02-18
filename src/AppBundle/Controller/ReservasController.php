<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\ExecutionIssuesFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\Type\ReservaType;
use AppBundle\Entity\Reserva;
use AppBundle\Entity\ReservaLog;
use AppBundle\Form\Type\ReservaFilterFormType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ReservasController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/reservas")
 */
class ReservasController extends Controller
{
    const FILTER_ATTR_NAME = 'reservas.filter';

    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $session = $this->container->get('session');
        $filter = $session->get(self::FILTER_ATTR_NAME);
        if (null === $filter) {
            $filter = array(
                'q' => '',
                'startAt' => array(
                    'left_date' => new \DateTime('now')
                ),
                'isExecuted' => 'no',
                'isCancelled' => 'no'
            );
            $session->set(self::FILTER_ATTR_NAME, $filter);
        }

        $form = $this->createForm(ReservaFilterFormType::class, $filter);

        return $this->render('@App/Reservas/index.html.twig', array(
            'form' => $form->createView(),
            'q' => $filter['q']
        ));
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->join('r.serviceType', 'st')
                ->join('r.provider', 'p')
                ->leftJoin('r.driver', 'd')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());
        $filter['q'] = $search['value'];

        if ($search['value'] && 1 === preg_match('/^(T|t)(?P<year>\d{1})(?P<month>\d{2})(?P<day>\d{2})(|-(?P<id>(\d{2}|\d{4})))$/', $search['value'], $matches)) {
            $andX = $qb->expr()->andX();

            $date = new \DateTime(sprintf('%s-%s-%s', substr(date('y'), 0, 1).$matches['year'], $matches['month'], $matches['day']));
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 00:00:00'))));
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 23:59:59'))));

            if (isset($matches['id'])) {
                $andX->add($qb->expr()->like('r.id', $qb->expr()->literal(sprintf('%%%s', ltrim($matches['id'], '0')))));
                if (2 === strlen($matches['id'])) {
                    $andX->add($qb->expr()->lte('r.id', $qb->expr()->literal(2493)));
                }
            }
            $qb->where($andX);
        } else {
            $andX = $qb->expr()->andX();

            if ($search['value']) {
                preg_match_all('/(?P<word>\w+)/', $search['value'], $matches);
                $wordX = $qb->expr()->andX();
                foreach ($matches['word'] as $word) {
                    $wordX->add($qb->expr()->like('r.clientNames', $qb->expr()->literal(sprintf('%%%s%%', $word))));
                }

                $andX->add($qb->expr()->orX(
                        $qb->expr()->like('r.providerReference', $qb->expr()->literal("%{$search['value']}%")),
                        $qb->expr()->like('d.name', $qb->expr()->literal("%{$search['value']}%")),
                        $wordX
                        ));
            }

            if ($andX->count() > 0) {
                $qb->where($andX);
            }

            $form = $this->createForm(ReservaFilterFormType::class);
            $form->submit($request->request->get($form->getName()));
            $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
        }

        $session = $this->container->get('session');
        $session->set(self::FILTER_ATTR_NAME, array_merge($filter, isset($form) ? $form->getData() : array()));

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'serialNumber' || $name == 'startAt') {
                    return 'r.startAt';
                } elseif ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'serviceType') {
                    return 'st.name';
                } elseif ($name == 'driver') {
                    return 'd.name';
                } elseif ($name == 'clientNames') {
                    return 'r.clientNames';
                } elseif ($name == 'pax') {
                    return 'r.pax';
                } elseif ($name == 'providerReference') {
                    return 'r.providerReference';
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

        $template = $this->container->get('twig')->load('@App/Reservas/_row.html.twig');
        $data = array_map(function(Reserva $record) use($template) {
            return array(
                $template->renderBlock('selector', array('record' => $record)),
                $template->renderBlock('state', array('record' => $record)),
                $template->renderBlock('inicio', array('record' => $record)),
                $template->renderBlock('provider', array('record' => $record)),
                $record->getSerialNumber(),
                $record->getProviderReference(),
                $record->getClientNames(),
                $template->renderBlock('pax', array('record' => $record)),
                $template->renderBlock('service', array('record' => $record)),
                $template->renderBlock('driver', array('record' => $record)),
                $template->renderBlock('guide', array('record' => $record)),
                $record->getIsExecuted() ? $record->getExecutionIssues() : null,
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
     * @Method({"get"})
     * @param Reserva $record
     * @return Response
     */
    public function viewAction(Reserva $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $logs = $manager->getRepository('AppBundle:ReservaLog')->findByReservaOrderedByCreatedAt($record);

        return $this->render('@App/Reservas/view.html.twig', array(
            'record' => $record,
            'logs' => $logs
        ));
    }

    /**
     * @Route("/nueva")
     * @Method({"get"})
     * @return Response
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ReservaType::class, new Reserva());

        return $this->render('@App/Reservas/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/nueva")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $reserva = new Reserva();
        $reserva->setEnterprise($this->getUser()->getEnterprises()->count() > 0 ? $this->getUser()->getEnterprises()[0] : $em->getRepository('AppBundle:Enterprise')->findOneBy(array()));
        $form = $this->createForm(ReservaType::class, $reserva);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $reserva->addLog(new ReservaLog());

            $em->persist($reserva);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', sprintf('Reservado con el número de confirmación %s.', $form->getData()->getSerialNumber()));

            return $this->redirect($this->generateUrl('app_reservas_index'));
        }

        return $this->render('@App/Reservas/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @param Reserva $reserva
     * @return Response
     */
    public function editAction(Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ReservaType::class, $record);

        return $this->render('@App/Reservas/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @param Reserva $reserva
     * @return Response
     */
    public function updateAction(Reserva $record, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ReservaType::class, $record);

        $originalPlaces = new ArrayCollection();
        foreach ($record->getPassingPlaces() as $p) {
            $originalPlaces->add($p);
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            foreach ($originalPlaces as $p) {
                if (!$record->getPassingPlaces()->contains($p)) {
                    $em->remove($p);
                }
            }

            $record->addLog(new ReservaLog());

            $em->flush();

            return $this->redirect($this->generateUrl('app_reservas_index'));
        }

        return $this->render('@App/Reservas/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @@Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @Security("is_granted('ROLE_OWNER')")
     * @param Reserva $record
     * @return Response
     */
    public function deleteAction(Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        if ($this->get('request')->isXmlHttpRequest()) {
            return new JsonResponse(array('result' => 'ok'));
        } else {
            $this->addFlash('notice',
                sprintf('La reserva %s ha sido eliminada.', $record->getSerialNumber()));
            return $this->redirect($this->generateUrl('app_reservas_index'));
        }
    }

    /**
     * @Route("/{id}/cancelar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $record
     * @return Response
     */
    public function cancelAction(Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();
        $record->setIsCancelled();
        $record->addLog(new ReservaLog());

        $em->flush();

        if ($this->get('request')->isXmlHttpRequest()) {
            return new JsonResponse(array('result' => 'ok'));
        } else {
            $this->addFlash('notice',
                sprintf('La reserva %s ha sido cancelada.', $record->getSerialNumber()));
            return $this->redirect($this->generateUrl('app_reservas_index'));
        }
    }

    /**
     * @Route("/{id}/ejecutar", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $record
     * @param Request $request
     * @return Response
     */
    public function executeAction(Reserva $record, Request $request)
    {
        $form = $this->createForm(ExecutionIssuesFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $record->setIsExecuted();
            $record->addLog(new ReservaLog());

            $em->flush();

            if ($this->get('request')->isXmlHttpRequest()) {
                return new JsonResponse(array('result' => 'ok'));
            } else {
                $this->container->get('session')->getFlashBag()->add('notice',
                        sprintf('La reserva %s ha sido ejecutada.', $record->getSerialNumber()));
                return $this->redirect($this->generateUrl('app_reservas_index'));
            }
        }

        return $this->render('@App/Reservas/execute.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/confirmar-conductor", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("reserva", class="AppBundle\Entity\Reserva")
     * @param Reserva $reserva
     * @return JsonResponse
     */
    public function confirmDriverAction(Reserva $reserva)
    {
        $reserva->setIsDriverConfirmed(true);
        $reserva->addLog(new ReservaLog());

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(array(
            'result' => 'ok'
        ));
    }

    /**
     * @Route("/obtener-listado-lugares")
     * @Method({"get"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlacesListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AppBundle:Place')
                ->createQueryBuilder('p')
                ->orderBy('p.name');

        if ($request->get('q')) {
            $qb->where($qb->expr()->like('p.name', $qb->expr()->literal("%{$request->get('q')}%")));
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb->getQuery(), $request->get('page', 1), 10);

        $total = $pagination->getTotalItemCount();

        $results = array(
            'results' => array(),
            'pagination' => array(
                'more' => ($request->get('page', 1) * 10) < $total
            )
        );

        foreach ($pagination as $record) {
            $results['results'][] = array(
                'id' => $record->getId(),
                'text' => $record->getName()
            );
        }

        return new JsonResponse($results);
    }
}
