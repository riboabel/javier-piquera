<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Description of PricesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/prices")
 */
class PricesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();

        $providers = $manager->createQuery('SELECT p FROM AppBundle:Provider p ORDER BY p.name');

        return $this->render('App/Prices/index.html.twig', array(
            'providers' => $providers
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
        $manager = $this->getDoctrine()->getManager();

        $qb = $manager->getRepository('AppBundle:ServiceType')
                ->createQueryBuilder('s')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());

        $andX = $qb->expr()->andX();

        if ($search['value']) {
            $andX->add($qb->expr()->like('s.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value']))));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'name') {
                    return 's.name';
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

        $twig = $this->container->get('twig');
        $repository = $manager->getRepository('AppBundle:Price');
        $provider = isset($filter['provider']) && $filter['provider'] ? $manager->find('AppBundle:Provider', $filter['provider']) : null;

        $data = array_map(function(\AppBundle\Entity\ServiceType $service) use($repository, $twig, $provider) {
            $template = $twig->load('App/Prices/_blocks.html.twig');

            $price = null !== $provider ? $repository->findOneBy(array(
                'provider' => $provider->getId(),
                'serviceType' => $service->getId()
            )) : null;

            return array(
                $service->getName(),
                $template->renderBlock('receivable', array(
                    'value' => $provider ? (null !== $price && null !== $price->getReceivableCharge() ? sprintf('%0.2f', $price->getReceivableCharge()) : '') : sprintf('%0.2f', $service->getDefaultPrice()),
                    'service' => $service,
                    'provider' => $provider
                )),
                $template->renderBlock('payable', array(
                    'value' => $provider ? (null !== $price && null !== $price->getPayableCharge() ? sprintf('%0.2f', $price->getPayableCharge()) : '') : sprintf('%0.2f', $service->getDefaultPayAmount()),
                    'service' => $service,
                    'provider' => $provider
                ))
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
     * @Route("/save", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request)
    {
        $value = $request->get('value');

        $pairs = explode(';', $request->get('id'));
        foreach ($pairs as $pair) {
            $parts = explode(':', $pair);
            $$parts[0] = $parts[1];
        }

        $manager = $this->getDoctrine()->getManager();
        $service = $manager->find('AppBundle:ServiceType', $service);
        $provider = $provider != 0 ? $manager->find('AppBundle:Provider', $provider) : null;

        if (null !== $provider) {
            $price = $manager->getRepository('AppBundle:Price')->findOneBy(array(
                'provider' => $provider->getId(),
                'serviceType' => $service->getId()
            ));
            if (!$price) {
                $price = new \AppBundle\Entity\Price();
                $price
                        ->setProvider($provider)
                        ->setServiceType($service)
                        ;
                $manager->persist($price);
            }
            $price
                    ->setReceivableCharge($type == 'receivable' ? ($value !== '' ? $value : null) : $price->getReceivableCharge())
                    ->setPayableCharge($type == 'payable' ? ($value !== '' ? $value : null) : $price->getPayableCharge())
                    ;

            if (null !== $price->getId() && null === $price->getPayableCharge() && null === $price->getReceivableCharge()) {
                $manager->remove($price);
            }
        } else {
            $service
                    ->setDefaultPrice($type == 'receivable' ? $value : $service->getDefaultPrice())
                    ->setDefaultPayAmount($type == 'payable' ? $value : $service->getDefaultPayAmount())
                    ;
        }

        $manager->flush();

        return new JsonResponse(array(
            'now' => $request->get('now'),
            'value' => is_numeric($value) ? sprintf('%0.2f', $value) : $value
        ));
    }

    /**
     * @Route("/print/{id}", defaults={"id": "\d*"}, options={"expose": true})
     * @Method({"get", "post"})
     * @param Request $request
     * @return StreamedResponse
     */
    public function printAction($id, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $provider = $manager->find('AppBundle:Provider', $id);

        $queryServices = $manager->getRepository('AppBundle:ServiceType')
                ->createQueryBuilder('s')
                ->orderBy('s.name')
                ;

        $form = $this->createFormBuilder()
                ->add('services', 'entity', array(
                    'class' => 'AppBundle:ServiceType',
                    'multiple' => true,
                    'query_builder' => $queryServices,
                    'required' => false
                ))
                ->getForm()
                ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $report = new \AppBundle\Lib\Reports\PricesByProvider($this->getDoctrine()->getManager(),
                    $provider, $form->get('services')->getData()->toArray());

            return new StreamedResponse(function() use($report) {
                file_put_contents('php://output', $report->getContent());
            }, 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="prices.pdf"'
            ));
        }

        return $this->render('App/Prices/form_print.html.twig', array(
            'form' => $form->createView(),
            'provider' => $provider
        ));
    }
}
