<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\ReservaLog;
use AppBundle\Form\Type\PayFilterFormType;

/**
 * Description of PaysController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/pagos")
 */
class PaysController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $acts = $em->createQuery('SELECT p FROM AppBundle:PayAct p ORDER BY p.createdAt')->getResult();
        $form = $this->createForm(PayFilterFormType::class, array('paidAt' => 'no-pagado'));

        return $this->render('App/Pays/index.html.twig', array(
            'charges' => $acts,
            'form' => $form->createView()
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
                ->join('r.driver', 'd')
                ;

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false))
        );

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            if (1 === preg_match('/^\d{4}$/', $search['value'])) {
                $orX->add($qb->expr()->andX(
                    $qb->expr()->gte('r.startAt', $qb->expr()->literal(sprintf('%s-01-01 00:00', $search['value']))),
                    $qb->expr()->lte('r.startAt', $qb->expr()->literal(sprintf('%s-12-31 23:59', $search['value'])))
                ));
            }

            $orX->add($qb->expr()->like('st.name', ':q'));
            $orX->add($qb->expr()->like('p.name', ':q'));
            $orX->add($qb->expr()->like('d.name', ':q'));
            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));

            $andX->add($orX);
        }

        $qb->where($andX);

        $form = $this->createForm(PayFilterFormType::class);
        $form->submit($request->request->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')
                ->addFilterConditions($form, $qb);

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'driver') {
                    return 'd.name';
                } elseif ($name == 'serviceType') {
                    return 'st.name';
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

        $getPayableCharge = function(\AppBundle\Entity\Reserva $record) use($em) {
            $price = $em->getRepository('AppBundle:Price')->findOneBy(array(
                'provider' => $record->getProvider()->getId(),
                'serviceType' => $record->getServiceType()->getId()
            ));

            if (null !== $price && null !== $price->getPayableCharge()) {
                $value = $price->getPayableCharge();
            } else {
                $value = $record->getServiceType()->getDefaultPayAmount();
            }

            return $value;
        };

        $template = $this->container->get('twig')->loadTemplate('App/Pays/_row.html.twig');

        $data = array_map(function(\AppBundle\Entity\Reserva $record) use ($getPayableCharge, $template) {
            $serviceString = sprintf('<span title="%s">%s</span>', $record->getPlainServiceDescription(),
                $record->getServiceType()->getName());

            $row = array(
                $template->renderBlock('select', array('record' => $record)),
                $template->renderBlock('start', array('record' => $record)),
                $record->getDriver()->getName(),
                (string) $record->getProvider(),
                $record->getProviderReference(),
                $serviceString,
                $template->renderBlock('amount', array('amount' => $getPayableCharge($record)))
            );

            return $row;
        }, $list);

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/preparar")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function prepareAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $query = $manager->createQuery('SELECT r, st, p FROM AppBundle:Reserva r JOIN r.serviceType st JOIN r.provider p WHERE r.id IN (:ids) ORDER BY r.startAt ASC')
            ->setParameter('ids', $request->get('ids'));
        $records = $query->getResult();

        $prices = array();
        foreach ($records as $record) {
            $price = $manager->getRepository('AppBundle:Price')->findOneBy(array(
                'provider' => $record->getProvider()->getId(),
                'serviceType' => $record->getServiceType()->getId()
            ));

            if (null !== $price && null !== $price->getPayableCharge()) {
                $prices[$record->getId()] = $price->getPayableCharge();
            } else {
                $prices[$record->getId()] = $record->getServiceType()->getDefaultPayAmount();
            }
        }

        return $this->render('App/Pays/prepare.html.twig', array(
            'records' => $records,
            'prices' => $prices
        ));
    }

    /**
     * @Route("/ejecutar")
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $request->get('ids');
        $prices = $request->get('prices');

        $query = $em->createQuery('SELECT r FROM AppBundle:Reserva r JOIN r.serviceType st WHERE r.id IN (:ids) ORDER BY r.startAt ASC')
            ->setParameter('ids', $request->get('ids'));
        $records = $query->getResult();

        $payAct = new \AppBundle\Entity\PayAct();
        $em->persist($payAct);

        foreach ($records as $record) {
            $record
                ->setPayAct($payAct)
                ->setDriverPayAmount($prices[array_search($record->getId(), $ids)])
                ->setPaidAt(new \DateTime('now'))
                ->addLog(new ReservaLog())
                ;
        }

        $em->flush();

        return $this->redirect($this->generateUrl('app_pays_index'));
    }

    /**
     * @Route("/imprimir-anterior")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $em->createQuery('SELECT r.id FROM AppBundle:Reserva r WHERE r.payAct = :act ORDER BY r.paidAt')
            ->setParameter('act', $request->get('id'))
            ->getResult();

        $report = new \AppBundle\Lib\Reports\Payrole(array(
            'ids' => array_map(function($id) {
                return $id['id'];
            }, $ids),
            'prices' => array()
        ), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }
}
