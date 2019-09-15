<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/14/2019
 * Time: 7:01 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\HAccommodation;
use AppBundle\Form\Type\ImportAccommodationFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccommodationController
 *
 * @Route("/accomodations")
 */
class AccommodationController extends Controller
{
    /**
     * @Route("/")
     * @Method("GET")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('App/Accommodation/index.html.twig');
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

        $qb = $manager->getRepository('AppBundle:HAccommodation')
            ->createQueryBuilder('a')
            ->join('a.provider', 'p')
            ->join('p.region', 'r')
        ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());
        $filter = $request->get('filter', array());
        $filter['q'] = $search['value'];

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'startDate') {
                    return 'a.startDate';
                } elseif ($name == 'endDate') {
                    return 'a.endDate';
                } elseif ($name == 'nights') {
                    return 'a.nights';
                } elseif ($name == 'reference') {
                    return 'a.reference';
                } elseif ($name == 'leadClient') {
                    return 'a.leadClient';
                } elseif ($name == 'pax') {
                    return 'a.pax';
                } elseif ($name == 'fromLocation') {
                    return 'p.name';
                } elseif ($name == 'fromRegion') {
                    return 'r.name';
                } elseif ($name == 'cost') {
                    return 'a.cost';
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

        $template = $this->container->get('twig')->load('App/Accommodation/_row.html.twig');
        $data = array_map(function(HAccommodation $record) use($template) {
            return [
                $record->getStartDate()->format('d/m/Y'),
                $record->getEndDate()->format('d/m/Y'),
                $record->getNights(),
                $template->renderBlock('reference', ['record' => $record]),
                $record->getLeadClient(),
                $record->getPax(),
                $record->getProvider()->getName(),
                $record->getProvider()->getRegion()->getName(),
                sprintf('%0.2f', $record->getCost()),
                $template->renderBlock('actions', ['record' => $record])
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
     * @Route("/{id}/delete", requirements={"id": "\d+"}, options={"expose": true})
     * @Method("POST")
     * @param HAccommodation $accommodation
     */
    public function deleteAction(HAccommodation $accommodation)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($accommodation);
        $manager->flush();

        $this->addFlash('notice', 'La reserva ha sido eliminada.');

        return $this->redirect($this->generateUrl('app_accommodation_index'));
    }

    /**
     * @Route("/importar")
     * @Method({"GEt", "POST"})
     * @param Request $request
     * @return Response
     */
    public function importAction(Request $request)
    {
        $form = $this->createForm(ImportAccommodationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get('app.services.accommodation_importer')->import(
                    $form->get('file')->getData(),
                    $form->get('removeBeforeImport')->getData(),
                    $form->get('year')->getData(),
                    $form->get('month')->getData()
                );

                $this->addFlash('notice', 'La importación se realizó correctamente.');
            } catch (\RuntimeException $e) {
                $this->addFlash('notice', 'Hubo un error en la importación.' . $e->getMessage());
            }

            return $this->redirect($this->generateUrl('app_accommodation_index'));
        }

        return $this->render('App/Accommodation/import.html.twig', [
            'form' => $form->createView()
        ]);
    }
}