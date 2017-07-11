<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Form\Type\InvoiceType;
use AppBundle\Entity\Reserva;
use AppBundle\Entity\Invoice;
use AppBundle\Form\Type\InvoiceFormType;
use AppBundle\Entity\Provider;

/**
 * Description of  InvoicesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/facturas")
 */
class InvoicesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return array
     */
    public function indexAction()
    {
        return $this->render('App/Invoices/index.html.twig');
    }

    /**
     * @Route("/obtener-datos", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Invoice')
                ->createQueryBuilder('n')
                ->join('n.provider', 'p')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $andX = $qb->expr()->andX();

            if (preg_match('/^\d{4}\/\d{4}$/', $search['value'])) {
                $andX->add($qb->expr()->eq('n.serialNumber', ':q'));
                $qb->setParameter('q', $search['value']);
            } else {
                $andX->add($qb->expr()->like('p.name', ':q'));
                $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
            }

            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'createdat') {
                    return 'n.createdAt';
                } elseif ($name == 'invoiceNumber') {
                    return 'n.serialNumber';
                } elseif ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'invoicedPrice') {
                    return 'n.totalCharge';
                } else {
                    return null;
                }
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

        $total = $pagination->getTotalItemCount();

        $template = $this->container->get('twig')->loadTemplate('App/Invoices/_row.html.twig');
        $data = array_map(function(Invoice $record) use($template) {
            return array(
                $template->renderBlock('createdat', array('record' => $record)),
                $record->getSerialNumber(),
                $record->getProvider()->getName(),
                $template->renderBlock('charge', array('record' => $record)),
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
     * @Route("/nuevo")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($invoice);

            $enterprise = $manager->getRepository('AppBundle:Enterprise')->findOneBy(array());
            $invoiceNumber = sprintf('%04d/%s', $enterprise->getLastInvoiceNumber() + 1, date('Y'));
            $enterprise->setLastInvoiceNumber($enterprise->getLastInvoiceNumber() + 1);

            $invoice->setSerialNumber($invoiceNumber);
            
            foreach ($form->get('lines') as $line) {
                $service = $line->get('service')->getData();
                $service
                        ->setInvoicedAt(new \DateTime('now'))
                        ->setInvoiceNumber($invoiceNumber)
                        ;
            }

            $manager->flush();

            return $this->redirectToRoute('app_invoices_index');
        }

        return $this->render('App/Invoices/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/get-services/{id}", requirements={"id": "\d+"}, options={"expose": true})
     * @Method({"get"})
     * @ParamConverter("provider", class="AppBundle\Entity\Provider")
     * @param Request
     * @return Response
     */
    public function getServicesAction(Provider $provider, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->join('r.provider', 'p')
                ->join('r.serviceType', 's')
                ->orderBy('r.startAt')
                ;
        $andX = $qb->expr()->andX(
                $qb->expr()->eq('p.id', ':provider'),
                $qb->expr()->isNull('r.invoiceNumber'),
                $qb->expr()->eq('r.isCancelled', 'false'),
                $qb->expr()->isNull('r.cobradoAt')
                );
        
        $qb->setParameter('provider', $provider->getId());

        if ($request->get('q')) {
            if (preg_match('#^T\d{5}\-\d{4}$#', $request->get('q'))) {

            } else {
                $andX->add($qb->expr()->orX(
                        $qb->expr()->andX(
                                $qb->expr()->isNotNull('r.clientNames'),
                                $qb->expr()->like('r.clientNames', ':q')
                                ),
                        $qb->expr()->andX(
                                $qb->expr()->isNotNull('r.providerReference'),
                                $qb->expr()->like('r.providerReference', ':q')
                                ),
                        $qb->expr()->like('s.name', ':q')
                        ));
                $qb->setParameter('q', sprintf('%%%s%%', $request->get('q')));
            }
        }

        $qb->where($andX);

        $paginator = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate($qb->getQuery(), $request->get('page', 1), 10);

        $results = array(
            'results' => array_map(function(Reserva $record) {
                return array(
                    'id' => $record->getId(),
                    'text' => sprintf('%s (%s)', $record->getServiceType()->getName(), $record->getSerialNumber()),
                    'serviceName' => $record->getServiceType()->getName(),
                    'clientNames' => $record->getClientNames(),
                    'reference' => $record->getProviderReference(),
                    'serialNumber' => $record->getSerialNumber(),
                    'price' => sprintf('%0.2f', $record->getClientPriceAmount())
                );
            }, $pagination->getItems()),
            'pagination' => array(
                'more' => ($request->get('page', 1) * 10) < $pagination->getTotalItemCount()
            )
        );

        return new JsonResponse($results);
    }
}
