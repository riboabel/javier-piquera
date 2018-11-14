<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 10/4/2018
 * Time: 4:54 PM
 */

namespace AppBundle\Controller\Control;

use AppBundle\Entity\ReservaTercero;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GuiaController
 *
 * @Route("/control/guia")
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class GuiaController extends Controller
{
    /**
     * @Route("/")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('App/Control/Guia/index.html.twig');
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $qb = $manager->getRepository('AppBundle:ReservaTercero')
            ->createQueryBuilder('r')
            ->join('r.serviceType', 'st')
            ->join('r.client', 'c')
            ->join('r.provider', 'p');

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        $andX = $qb->expr()->andX(
            $qb->expr()->eq('r.type', ':type'),
            $qb->expr()->eq('r.state', $qb->expr()->literal(ReservaTercero::STATE_CREATED))
        );
        $qb->setParameter('type', ReservaTercero::TYPE_GUIA);

        $startAt = new \DateTime('tomorrow');
        $endAt = date_create($startAt->format('Y-m-d 23:59:59'));
        if (6 == date('w')) {
            $endAt->add(new \DateInterval('P1D'));
        }

        $andX->add($qb->expr()->gte('r.startAt', $qb->expr()->literal($startAt->format('Y-m-d H:i:s'))));
        $andX->add($qb->expr()->lte('r.startAt', $qb->expr()->literal($endAt->format('Y-m-d H:i:s'))));

        if (isset($search['value']) && $search['value'] != '') {
            $orX = $qb->expr()->orX(
                $qb->expr()->like('r.clientSerial', ':q'),
                $qb->expr()->like('r.clientNames', ':q'),
                $qb->expr()->like('st.name', ':q'),
                $qb->expr()->like('c.name', ':q'),
                $qb->expr()->like('p.name', ':q')
            );
            $andX->add($orX);

            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        // Aqui va el order

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

        $list = $pagination->getItems();
        $total = $pagination->getTotalItemCount();

        $renderer = $this->container->get('twig');
        $template = $renderer->load('App/Control/Guia/_row.html.twig');
        $data = array_map(function(ReservaTercero $record) use ($template) {
            return array(
                $template->renderBlock('start_at', array('record' => $record)),
                $record->getProviderSerial(),
                $record->getClient()->getName(),
                $record->getClientSerial(),
                $record->getProvider()->getName(),
                (string) $record->getServiceType(),
                $template->renderBlock('controls', array(
                    'record' => $record
                ))
            );
        }, $list);

        return new JsonResponse(array(
            'data' => $data,
            'draw' => (int) $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @param Reserva $record
     * @param Request $request
     * @return Response
     */
    public function editAction(ReservaTercero $record, Request $request)
    {
        $form = $this->createFormBuilder($record)
            ->add('control1', ChoiceType::class, array(
                'choices' => array(
                    'NO' => 'NO',
                    'SÍ' => 'SÍ'
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

        return $this->render('App/Control/Guia/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}