<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\Type\ReservaType;
use AppBundle\Entity\Reserva;
use AppBundle\Entity\ReservaLog;

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
                    'from' => date_create('now')->format('d/m/Y'),
                    'to' => ''
                ),
                'isExecuted' => 'no',
                'isCancelled' => 'no',
                'isDriverConfirmed' => '',
                'isDriverAssigned' => '',
                'withDrivers' => array()
            );
            $session->set(self::FILTER_ATTR_NAME, $filter);
        }

        $manager = $this->getDoctrine()->getManager();
        $drivers = $manager->createQuery('SELECT d FROM AppBundle:Driver d ORDER BY d.name');

        return $this->render('App/Reservas/index.html.twig', array(
            'filter' => $filter,
            'drivers' => $drivers
        ));
    }

    /**
     * @Route("/get-data")
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
                ->leftJoin('r.driver', 'd');

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());
        $filter['q'] = $search['value'];

        $this->container->get('session')->set(self::FILTER_ATTR_NAME, $filter);

        $andX = $qb->expr()->andX();
        if (isset($filter['startAt']['from']) && $filter['startAt']['from']) {
            $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal(date_create_from_format('d/m/Y', $filter['startAt']['from'])->format('Y-m-d 00:00:00'))));
        }
        if (isset($filter['startAt']['to']) && $filter['startAt']['to']) {
            $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal(date_create_from_format('d/m/Y', $filter['startAt']['to'])->format('Y-m-d 23:59:59'))));
        }
        if (isset($filter['isExecuted']) && $filter['isExecuted']) {
            $andX->add($qb->expr()->eq('r.isExecuted', $qb->expr()->literal($filter['isExecuted'] == 'yes')));
        }
        if (isset($filter['isCancelled']) && $filter['isCancelled']) {
            $andX->add($qb->expr()->eq('r.isCancelled', $qb->expr()->literal($filter['isCancelled'] == 'yes')));
        }
        if (isset($filter['isDriverConfirmed']) && $filter['isDriverConfirmed']) {
            $andX->add($qb->expr()->eq('r.isDriverConfirmed', $qb->expr()->literal($filter['isDriverConfirmed'] == 'yes')));
        }
        if (isset($filter['isDriverAssigned']) && $filter['isDriverAssigned']) {
            if ($filter['isDriverAssigned'] === 'with-drivers') {
                if (isset($filter['withDrivers']) && $filter['withDrivers']) {
                    $andX->add($qb->expr()->orX(
                        $qb->expr()->isNull('r.driver'),
                        $qb->expr()->in('d.id', $filter['withDrivers'])
                    ));
                } else {
                    $andX->add($qb->expr()->isNull('r.driver'));
                }
            } elseif ($filter['isDriverAssigned'] == 'no') {
                $andX->add($qb->expr()->isNull('r.driver'));
            } else {
                $andX->add($qb->expr()->isNotNull('r.driver'));
            }

        }

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            $matches = array();

            if (1 === preg_match('/^(T|t)(?P<year>\d{1})(?P<month>\d{2})(?P<day>\d{2})(|-(?P<id>(\d{2}|\d{4})))$/', $search['value'], $matches)) {
                $date = new \DateTime(sprintf('%s-%s-%s', substr(date('y'), 0, 1).$matches['year'], $matches['month'], $matches['day']));
                $andKX = $qb->expr()->andX(
                    $qb->expr()->gte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 00:00:00'))),
                    $qb->expr()->lte('r.startAt', $qb->expr()->literal($date->format('Y-m-d 23:59:59')))
                );
                if (isset($matches['id'])) {
                    $andKX->add($qb->expr()->like('r.id', $qb->expr()->literal(sprintf('%%%s', ltrim($matches['id'], '0')))));
                    if (2 === strlen($matches['id'])) {
                        $andX->add($qb->expr()->lte('r.id', $qb->expr()->literal(2493)));
                    }
                }
                $orX->add($andKX);
            } else {
                $orX->add($qb->expr()->like('r.providerReference', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('st.name', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('p.name', $qb->expr()->literal("%{$search['value']}%")));
                $orX->add($qb->expr()->like('d.name', $qb->expr()->literal("%{$search['value']}%")));

                preg_match_all('/(?P<word>\w+)/', $search['value'], $matches);
                $wordX = $qb->expr()->andX();
                foreach ($matches['word'] as $word) {
                    $wordX->add($qb->expr()->like('r.clientNames', $qb->expr()->literal(sprintf('%%%s%%', $word))));
                }
                $orX->add($wordX);
            }

            if ($orX->count() > 0) {
                $andX->add($orX);
            }
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
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

        if ($request->get('length')) {
            $paginator = $this->get('knp_paginator');
            $page = $request->get('start', 0) / $request->get('length') + 1;
            $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

            $list = $pagination->getItems();
            $total = $pagination->getTotalItemCount();
        } else {
            $list = $qb->getQuery()->getResult();
            $total = count($list);
        }

        $renderer = $this->container->get('twig');
        $template = $renderer->loadTemplate('App/Reservas/_row.html.twig');
        $data = array_map(function(Reserva $record) use ($template) {
            $stateString = '';
            if ($record->getIsCancelled()) {
                $stateString = '<i class="glyphicon glyphicon-ban-circle" title="Cancelada"></i>';
            } elseif ($record->getIsExecuted()) {
                $stateString = '<i class="fa fa-check" title="Ejecutada"></i>';
            }

            $driverString = '';
            if ($record->getDriver()) {
                $driverString = sprintf('<span title="%s" class="text-%s">%s</span>',
                    $record->getDriver()->getContactInfo(),
                    $record->getIsDriverConfirmed() ? 'success' : 'danger',
                    $record->getDriver()->getName());
            }

            $serviceString = sprintf('<span title="%s">%s</span>', $record->getServiceDescription(),
                $record->getServiceType()->getName());

            return array(
                $template->renderBlock('selector', array(
                    'record' => $record
                )),
                $stateString,
                sprintf('<div title="%s">%s</div>',
                    sprintf("%s: %s a %s: %s",
                        $record->getStartPlace()->getName(),
                        $record->getStartPlace()->getPostalAddress(),
                        $record->getEndPlace()->getName(),
                        $record->getEndPlace()->getPostalAddress()),
                    $record->getStartAt()->format('d/m/Y H:i')),
                sprintf('<div title="%s">%s</div>', $record->getProvider()->getContactInfo(),
                    $record->getProvider()->getName()),
                $record->getSerialNumber(),
                $record->getProviderReference(),
                $record->getClientNames(),
                sprintf('<div class="center">%s</div>', $record->getPax()),
                $serviceString,
                $driverString,
                $template->renderBlock('guide', array('record' => $record)),
                $record->getIsExecuted() ? $record->getExecutionIssues() : null,
                $template->renderBlock('actions', array('record' => $record))
            );
        }, $list);

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
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $record
     * @return Response
     */
    public function viewAction(Reserva $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $logs = $manager->getRepository('AppBundle:ReservaLog')->findByReservaOrderedByCreatedAt($record);

        return $this->render('App/Reservas/view.html.twig', array(
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

        return $this->render('App/Reservas/new.html.twig', array(
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

        return $this->render('App/Reservas/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $reserva
     * @return Response
     */
    public function editAction(Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ReservaType::class, $record);

        return $this->render('App/Reservas/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $reserva
     * @return Response
     */
    public function updateAction(Reserva $record, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ReservaType::class, $record);

        $originalPlaces = new \Doctrine\Common\Collections\ArrayCollection();
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

        return $this->render('App/Reservas/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @@Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $record
     * @return RedirectResponse
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
     * @return RedirectResponse
     */
    public function executeAction(Reserva $record, Request $request)
    {
        $form = $this->createForm(new \AppBundle\Form\Type\ExecutionIssuesFormType(), $record);

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

        return $this->render('App/Reservas/execute.html.twig', array(
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
