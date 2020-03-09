<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/14/2019
 * Time: 7:01 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\HAccommodation;
use AppBundle\Form\Type\AccommodationFilterFormType;
use AppBundle\Form\Type\AccommodationFormType;
use AppBundle\Form\Type\ImportAccommodationFormType;
use Carbon\Carbon;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $session = $this->container->get('session');
        $data = $session->get('accommodation.index.filter', []);

        $filterForm = $this->createForm(AccommodationFilterFormType::class, $data);

        return $this->render('App/Accommodation/index.html.twig', [
            'filter' => $filterForm->createView(),
            'display_length' => isset($data['_']['length']) ? $data['_']['length'] : 10,
            'display_start' => isset($data['_']['start']) ? $data['_']['start'] : 0
        ]);
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

        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        $filter = $this->createForm(AccommodationFilterFormType::class);
        $filter->submit($request->query->get($filter->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filter, $qb);

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

        $session = $this->container->get('session');
        $filterData = $filter->getData();
        $filterData['_']['length'] = $request->get('length');
        $filterData['_']['start'] = $request->get('start', 0);
        $session->set('accommodation.index.filter', $filterData);

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
                $template->renderBlock('paid_at', ['record' => $record]),
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
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param HAccommodation $accommodation
     * @return Response
     */
    public function editAction(HAccommodation $accommodation, Request $request)
    {
        $form = $this->createForm(AccommodationFormType::class, $accommodation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $this->addFlash('notice', 'La reserva se guardó.');

            return $this->redirect($this->generateUrl('app_accommodation_index'));
        }

        return $this->render(':App/Accommodation:edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method("POST")
     * @param HAccommodation $accommodation
     * @return RedirectResponse
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

    /**
     * @Route("/{id}/pagar", requirements={"id": "\d+"}, options={"expose": true})
     * @Method("POST")
     * @param HAccommodation $accommodation
     * @return JsonResponse
     */
    public function payAction(HAccommodation $accommodation, Request $request)
    {
        $op = $request->get('op');
        $manager = $this->getDoctrine()->getManager();
        $accommodation->setPaidAt($op ? null : new \DateTime('now'));
        $manager->flush();

        return new JsonResponse(['result' => 'success']);
    }

    /**
     * @Route("/payticket")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function payTicketAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $providers = [];
        foreach ($manager->createQuery('SELECT p.id, p.name FROM AppBundle:HProvider AS p ORDER BY p.name')->getResult() as $p) {
            $providers[$p['id']] = $p['name'];
        }

        $form = $this->createFormBuilder()
            ->add('startDate', DateRangeFilterType::class, [
                'label' => 'Inicio',
                'left_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Desde'
                ],
                'right_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Hasta'
                ]
            ])
            ->add('provider', ChoiceFilterType::class, [
                'label' => 'Hospedaje',
                'choices' => $providers,
                'multiple' => true,
                'required' => true,
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $expression = $filterQuery->getExpr()->in('p.id', $values['value']);

                    return $filterQuery->createCondition($expression);
                }
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = [];

            $queryBuilder = $manager->getRepository('AppBundle:HAccommodation')
                ->createQueryBuilder('a')
                ->join('a.provider', 'p');

            $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $queryBuilder);

            $queryBuilder->andWhere($queryBuilder->expr()->isNull('a.paidAt'));

            foreach ($queryBuilder->getQuery()->getResult() as $a) {
                if (!isset($data[$a->getProvider()->getId()])) {
                    $data[$a->getProvider()->getId()] = [
                        'name' => $a->getProvider()->getName(),
                        'records' => []
                    ];
                }
                $data[$a->getProvider()->getId()]['records'][] = $a;
            }

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($this->renderView('App/Accommodation/payticket_report.html.twig', ['data' => $data]));
            $dompdf->setPaper('LETTER');
            $dompdf->render();

            return new StreamedResponse(function() use($dompdf) {
                file_put_contents('php://output', $dompdf->output());
            }, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="pay-ticket.pdf"'
            ]);

        }

        return $this->render('App/Accommodation/payticket.html.twig', [
            'form' => $form->createView(),
            'action' => $this->generateUrl('app_accommodation_payticket')
        ]);
    }

    /**
     * @Route("/reporte")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function reportAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $providers = [];
        foreach ($manager->createQuery('SELECT p.id, p.name FROM AppBundle:HProvider AS p ORDER BY p.name')->getResult() as $p) {
            $providers[$p['id']] = $p['name'];
        }

        $form = $this->createFormBuilder()
            ->add('startDate', DateRangeFilterType::class, [
                'label' => 'Inicio',
                'left_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Desde'
                ],
                'right_date_options' => [
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text',
                    'label' => 'Hasta'
                ]
            ])
            ->add('provider', ChoiceFilterType::class, [
                'label' => 'Hospedaje',
                'choices' => $providers,
                'multiple' => true,
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $expression = $filterQuery->getExpr()->in('p.id', $values['value']);

                    return $filterQuery->createCondition($expression);
                }
            ])
            ->add('region', EntityFilterType::class, [
                'label' => 'Región',
                'multiple' => true,
                'class' => 'AppBundle:HRegion',
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $expression = $filterQuery->getExpr()->in('r.id', ':q_region');

                    return $filterQuery->createCondition($expression, ['q_region' => $values['value']]);
                }
            ])
            ->add('sortBy', ChoiceFilterType::class, [
                'label' => 'Ordenar por',
                'required' => true,
                'choices' => [
                    1 => 'hospedaje',
                    2 => 'inicio',
                    3 => 'referencia',
                    4 => 'región'
                ],
                'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $queryBuilder = $filterQuery->getQueryBuilder();
                    $alias = $filterQuery->getRootAlias();
                    if ($values['value'] == 1) {
                        $queryBuilder->orderBy('p.name');
                    } elseif ($values['value'] == 2) {
                        $queryBuilder->orderBy('a.startDate');
                    } elseif ($values['value'] == 3) {
                        $queryBuilder->orderBy('a.reference');
                    } elseif ($values['value'] == 4) {
                        $queryBuilder->orderBy('r.name');
                    }

                    return null;
                }
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $queryBuilder = $manager->getRepository('AppBundle:HAccommodation')
                ->createQueryBuilder('a')
                ->join('a.provider', 'p')
                ->join('p.region', 'r');

            $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $queryBuilder);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($this->renderView('App/Accommodation/report.html.twig', [
                'records' => $queryBuilder->getQuery()->getResult()
            ]));
            $dompdf->setPaper('LETTER', 'landscape');
            $dompdf->render();

            return new StreamedResponse(function() use($dompdf) {
                file_put_contents('php://output', $dompdf->output());
            }, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="accommodation-report.pdf"'
            ]);

        }

        return $this->render('App/Accommodation/payticket.html.twig', [
            'form' => $form->createView(),
            'action' => $this->generateUrl('app_accommodation_report')
        ]);
    }
}