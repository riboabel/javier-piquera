<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Reserva;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Description of ControlController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/control")
 */
class ControlController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $manager = $this->container->get('doctrine')->getManager();
        $tomorrow = new \DateTime('tomorrow');
        $query = $manager->createQuery('SELECT r FROM AppBundle:Reserva AS r JOIN r.driver AS d ' .
                'WHERE r.isCancelled = :false AND r.isExecuted = :false ' .
                'AND r.startAt >= :startAt AND r.startAt <= :endAt')
                ->setParameters(array(
                    'false' => false,
                    'startAt' => $tomorrow->format('Y-m-d H:i:s'),
                    'endAt' => $tomorrow->format('Y-m-d 23:59:59')
                ));

        return $this->render('App/Control/index.html.twig', array(
            'records' => $query
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
                ->leftJoin('r.driver', 'd');

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('r.isExecuted', $qb->expr()->literal(false)),
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false))
                );

        $startAt = new \DateTime('tomorrow');
        $endAt = date_create($startAt->format('Y-m-d 23:59:59'));
        if (6 == date('w')) {
            $endAt->add(new \DateInterval('P1D'));
        }

        $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($startAt->format('Y-m-d H:i:s'))));
        $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($endAt->format('Y-m-d H:i:s'))));

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
        $template = $renderer->loadTemplate('App/Control/_row.html.twig');
        $data = array_map(function(Reserva $record) use ($template) {
            return array(
                $template->renderBlock('start_at', array('record' => $record)),
                $template->renderBlock('provider', array('record' => $record)),
                $record->getSerialNumber(),
                $record->getProviderReference(),
                $record->getClientNames(),
                $template->renderBlock('pax', array('record' => $record)),
                $template->renderBlock('service', array('record' => $record)),
                $template->renderBlock('driver', array('record' => $record)),
                $template->renderBlock('guide', array('record' => $record)),
                $template->renderBlock('controls', array('record' => $record))
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
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $record
     * @param Request $request
     * @return Response
     */
    public function editAction(Reserva $record, Request $request)
    {
        $form = $this->createFormBuilder($record)
                ->add('control1', ChoiceType::class, array(
                    'choices' => array(
                        'SÍ' => 'SÍ',
                        'NO' => 'NO'
                    ),
                    'label' => '¿Hubo modificaciones?',
                    'required' => true,
                    'choices_as_values' => true
                ))
                ->add('control2', ChoiceType::class, array(
                    'choices' => array(
                        'SMS' => 'SMS',
                        'LLAMADA' => 'LLAMADA',
                        'OTRO' => 'OTRO'
                    ),
                    'label' => 'Aviso enviado por vía',
                    'required' => true,
                    'choices_as_values' => true
                ))
                ->getForm()
                ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return new Response('<script>$(".modal").modal("hide"); $("#table-xs").DataTable().draw(false);</script>');
        }

        return $this->render('App/Control/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
