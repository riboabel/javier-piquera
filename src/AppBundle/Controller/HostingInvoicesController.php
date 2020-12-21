<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/19/2020
 * Time: 6:59 p.m.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\HostingInvoice;
use AppBundle\Entity\HostingInvoiceProvider;
use AppBundle\Form\Type\HostingInvoiceFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class HostingInvoices
 *
 * @Route("/facturas-hospedaje")
 */
class HostingInvoicesController extends Controller
{
    /**
     * @Route("/", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('App/HostingInvoices/index.html.twig');
    }

    /**
     * @param Request $request
     * @Route("/obtener-datos", methods={"GET"}, options={"expose": true})
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:HostingInvoice')
            ->createQueryBuilder('i')
        ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $andX = $qb->expr()->andX();

            $andX->add($qb->expr()->like('i.invoiceNumber', ':q'));
            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));

            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'createdat') {
                    return 'i.createdAt';
                } elseif ($name == 'invoiceNumber') {
                    return 'i.serialNumber';
                } elseif ($name == 'provider') {
                    return 'i.providerNname';
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

        $template = $this->container->get('twig')->load('App/HostingInvoices/_row.html.twig');
        $data = array_map(function(HostingInvoice $record) use($template) {
            return [
                'createdAt' => $record->getCreatedAt()->format('Y-m-d H:i:s'),
                'invoiceNumber' => $record->getInvoiceNumber(),
                'providerName' => $record->getProviderName(),
                'grandTotal' => (float) $record->getGrandTotal(),
                'actions' => $template->renderBlock('actions', ['record' => $record])
            ];
        }, $pagination->getItems());

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/nuevo", methods={"GET", "POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $invoice = new HostingInvoice();
        $form = $this->createForm(HostingInvoiceFormType::class, $invoice);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($invoice);

            $this->generateNumber($invoice);

            $this->updateAccommodation(array_map(function($line) {
                return (integer) $line->accommodationId;
            }, $form->get('lines')->getData()->toArray()), $invoice->getInvoiceNumber());

            $manager->flush();

            return $this->redirectToRoute('app_hostinginvoices_index');
        }

        return $this->render('App/HostingInvoices/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/obtener-servicios/{id}", methods={"GET"}, requirements={"id": "\d+"}, options={"expose": true})
     * @param HostingInvoiceProvider $provider
     */
    public function getServicesAction(HostingInvoiceProvider $provider)
    {
        $manager = $this->getDoctrine()->getManager();
        $region = $provider->getRegion();
$dql = <<<DQL
SELECT a
FROM AppBundle:HAccommodation AS a
    JOIN a.provider AS p
    JOIN p.region AS r
WHERE a.paidAt IS NOT NULL
    AND r.id = :region
    AND a.invoiceNumber IS NULL
ORDER BY a.startDate
DQL;

        $services = $manager->createQuery($dql)
            ->setParameter('region', $region->getId())
            ->getResult();

        $result = [
            'services' => array_map(function($service) {
                return [
                    'booking_reference' => $service->getReference(),
                    'service' => $service->getProvider()->getName(),
                    'client_name' => $service->getLeadClient(),
                    'start_date' => $service->getStartDate()->format('Y-m-d'),
                    'end_date' => $service->getEndDate()->format('Y-m-d'),
                    'nights' => $service->getNights(),
                    'row_total' => (float) $service->getCost(),
                    'accommodation_id' => $service->getId()
                ];
            }, $services)
        ];

        return new JsonResponse($result);
    }

    /**
     * @Route("/{id}/imprimir", methods={"GET"}, requirements={"id": "\d+"})
     * @param HostingInvoice $invoice
     * @return Response
     */
    public function printAction(HostingInvoice $invoice)
    {
        $pdf = new \AppBundle\Lib\Reports\HostingInvoice($invoice);

        return new StreamedResponse(function() use ($pdf) {
            file_put_contents('php://output', $pdf->getContent());
        }, Response::HTTP_OK, [
            'Content-Type' => 'application/octet-stream',
            'Content-Description' => 'Factura de hospedaje',
            'Content-Disposition' => sprintf('attachment; filename=factura-hospedaje-%s.pdf', $invoice->getInvoiceNumber())
        ]);
    }

    private function generateNumber(HostingInvoice $invoice)
    {
        $manager = $this->getDoctrine()->getManager();

        $provider = $manager->find('AppBundle:HostingInvoiceProvider', $invoice->getProvider()->getId());

        $number = sprintf("%s%s%'02d", $provider->getPrefix(), date('y'), $provider->getNextAutoincrement());

        $provider->setNextAutoincrement($provider->getNextAutoincrement() + 1);

        $invoice->setInvoiceNumber($number);
    }

    private function updateAccommodation(array $ids, $invoiceNumber)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:HAccommodation');
        foreach ($ids as $id) {
            $record = $repository->find($id);

            if ($record) {
                $record->setInvoiceNumber($invoiceNumber);
            }
        }
    }
}