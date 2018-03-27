<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Driver;
use AppBundle\Entity\ServiceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Lib\Reports;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Reserva;
use AppBundle\Form\Type\ICheckType;

/**
 * Description of ReportsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/informes")
 */
class ReportsController extends Controller
{
    /**
     * @Route("/reservas-entre-fechas", name="app_reports_all_reservas_between_dates")
     * @Method({"get", "post"})
     * @param Request
     * @return Response
     */
    public function reservasBetweenDatesAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
                ->add('fromDate', DateType::class, array(
                    'label'     => 'Desde',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ))
                ->add('toDate', DateType::class, array(
                    'label'     => 'Hasta',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ))
                ->add('services', EntityType::class, array(
                    'multiple' => true,
                    'class' => \AppBundle\Entity\ServiceType::class,
                    'query_builder' => function(EntityRepository $repository) {
                        return $repository->createQueryBuilder('st')
                            ->orderBy('st.name');
                    },
                    'required' => false,
                    'label' => 'Servicios'
                ))
                ->add('includePlacesAddress', ICheckType::class, array(
                    'label' => 'Incluir direcciones',
                    'required' => false
                ))
                ->add('showProviderLogoIfPossible', ICheckType::class, array(
                    'label' => 'Mostrar logotipo de agencia',
                    'required' => false,
                    'data' => true
                ))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $report = new Reports\ServicesBetweenDatesReport($form->getData(),
                        $manager, $this->container->getParameter('vich_uploader.mappings')['logos']['upload_destination']);

                return new StreamedResponse(function() use($report) {
                    file_put_contents('php://output', $report->getContent());
                }, 200, array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="prices.pdf"'
                ));
            }
        }

        return $this->render('App/Reports/form_reservas_between_dates.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/reservas-por-agencia", name="app_reports_all_reservas_by_provider")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function reservasByProviderAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('fromDate', DateType::class, array(
                'label'     => 'Desde',
                'required'  => false,
                'widget'    => 'single_text',
                'format'    => 'd/M/y'
            ))
            ->add('toDate', DateType::class, array(
                'label'     => 'Hasta',
                'required'  => false,
                'widget'    => 'single_text',
                'format'    => 'd/M/y'
            ))
            ->add('provider', EntityType::class, array(
                'class' => \AppBundle\Entity\Provider::class,
                'label' => 'Agencia',
                'query_builder' => function(EntityRepository $repository) {
                    return $repository->createQueryBuilder('p')
                        ->orderBy('p.name');
                }
            ))
            ->add('services', EntityType::class, array(
                'label' => 'Servicios',
                'multiple' => true,
                'class' => \AppBundle\Entity\ServiceType::class,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository->createQueryBuilder('st')
                        ->orderBy('st.name');
                },
                'required' => false
            ))
            ->add('showProviderLogoIfPossible', ICheckType::class, array(
                'label' => 'Mostrar logotipo de agencia',
                'required' => false,
                'data' => true
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $report = new Reports\ServicesByProviderReport($form->getData(), $manager, $this->container->getParameter('vich_uploader.mappings')['logos']['upload_destination']);

                return new StreamedResponse(function() use($report) {
                    file_put_contents('php://output', $report->getContent());
                }, 200, array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="prices.pdf"'
                ));
            }
        }

        return $this->render('App/Reports/form_reservas_by_provider.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/reservas-por-conductor", name="app_reports_all_reservas_by_driver")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function reservasByDriverAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('fromDate', DateType::class, array(
                'label'     => 'Desde',
                'required'  => false,
                'widget'    => 'single_text',
                'format'    => 'd/M/y'
            ))
            ->add('toDate', DateType::class, array(
                'label'     => 'Hasta',
                'required'  => false,
                'widget'    => 'single_text',
                'format'    => 'd/M/y'
            ))
            ->add('drivers', EntityType::class, array(
                'class' => Driver::class,
                'label' => 'Conductor',
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('d')
                        ->where('d.enabled = true')
                        ->orderBy('d.name');
                },
                'multiple' => true
            ))
            ->add('includePlacesAddress', ICheckType::class, array(
                'label' => 'Incluir direcciones',
                'required' => false
            ))
            ->add('serviceType', EntityType::class, array(
                'class' => ServiceType::class,
                'label' => 'Servicio',
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('s')
                        ->orderBy('s.name');
                },
                'required' => false,
                'multiple' => true
            ))
            ->add('showProviderLogoIfPossible', ICheckType::class, array(
                'label' => 'Mostrar logotipo de agencia',
                'required' => false,
                'data' => true
            ))
            ->add('includeAllRecords', ICheckType::class, array(
                'label' => 'Incluir todos los servicios',
                'required' => false,
                'data' => true
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $report = new Reports\ServicesByDriverReport($form->getData(), $em, $this->container->getParameter('vich_uploader.mappings')['logos']['upload_destination']);

                return new StreamedResponse(function() use($report) {
                    file_put_contents('php://output', $report->getContent());
                }, 200, array('Content-Type' => 'application/pdf'));
            }
        }

        return $this->render('App/Reports/form_reservas_by_driver.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/reservas-seleccionadas", name="app_reports_print_selection")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printSelectionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\ServicesBySelection($request->get('ids', array()), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/reservas-seleccionadas-guidedriver", name="app_reports_print_special_selection")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printSpcialSelectionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\ServicesBySelectionSpecialReport($request->get('ids', array()), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/modelo-de-pago", name="app_reports_print_pay_form")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printPayroleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\Payrole(array(
            'ids' => $request->get('ids', array()),
            'prices' => $request->get('prices', array()),
            'showLogosIfPosible' => $request->get('logos') === 'yes'
        ), $em, $this->container->getParameter('kernel.root_dir').'/../web/uploads/logos');

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/modelo-de-cobro", name="app_reports_charge_report")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printChargeReportAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $report = new Reports\ChargeForm(array(
            'ids' => $request->get('ids', array()),
            'prices' => $request->get('prices', array())
        ), $manager, $this->container->getParameter('kernel.root_dir').'/../web/uploads/logos');

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/imprimir-factura/{serialNumber}", requirements={"serialNumber": "\d{4}\/\d{4}"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Invoice")
     * @param \AppBundle\Entity\Invoice $record
     * @return Response
     */
    public function printInvoiceAction(\AppBundle\Entity\Invoice $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $enterprise = $manager->getRepository('AppBundle:Enterprise')->findOneBy(array());

        $report = new Reports\Invoice(array(
            'record' => $record,
            'logo_path' => null !== $enterprise->getLogoName() ? sprintf('%s/../web/uploads/logos/%s', $this->container->getParameter('kernel.root_dir'), $enterprise->getLogoName()) : null
        ), $manager);

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/imprimir-orden-de-trabajo/{serialNumber}", requirements={"serialNumber": "T\d{5}-\d{4}"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva", options={"repository_method": "findBySerialNumber", "map_method_signature": true})
     * @param \AppBundle\Entity\Reserva $record
     * @return Respnse
     */
    public function printJobOrderAction(\AppBundle\Entity\Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\JobOrder(array(
            'record' => $record,
            'logo_path' => $this->container->getParameter('kernel.root_dir').'/../web/uploads/logos'
        ), $em);

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/imprimir-orden-de-trabajo-en-blanco/{id}", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param \AppBundle\Entity\Reserva $record
     * @return Respnse
     */
    public function printEmptyJobOrderAction(\AppBundle\Entity\Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\EmptyJobOrder(array(
            'record' => $record,
            'logo_path' => $this->container->getParameter('kernel.root_dir').'/../web/uploads/logos'
        ), $em);

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/imprimir-cobros-anteriores-por-proveedor", name="app_reports_old_cobros")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function printOldCobrosAction(Request $request)
    {
        $form = $this->createFormBuilder()
                ->add('fromDate', DateType::class, array(
                    'label'     => 'Desde',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ))
                ->add('toDate', DateType::class, array(
                    'label'     => 'Hasta',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $report = new Reports\CobrosReport($em, $data['fromDate'], $data['toDate']);

            return new StreamedResponse(function() use($report) {
                file_put_contents('php://output', $report->getContent());
            }, 200, array('Content-Type' => 'application/pdf'));
        }

        return $this->render('App/Reports/form_old_cobros.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/imprimir-pagos-anteriores-por-conductor", name="app_reports_old_pays")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function printOldPaysAction(Request $request)
    {
        $form = $this->createFormBuilder()
                ->add('fromDate', DateType::class, array(
                    'label'     => 'Desde',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ))
                ->add('toDate', DateType::class, array(
                    'label'     => 'Hasta',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'd/M/y'
                ))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $report = new Reports\PaysReport($em, $data['fromDate'], $data['toDate']);

            return new StreamedResponse(function() use($report) {
                file_put_contents('php://output', $report->getContent());
            }, 200, array('Content-Type' => 'application/pdf'));
        }

        return $this->render('App/Reports/form_old_pays.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/mostrar-formulario-imprimir-programa")
     * @Method({"get"})
     * @return Response
     */
    public function showProgramModelFormAction()
    {
        return $this->render('App/Reports/form_program_model.html.twig');
    }

    /**
     * @Route("/obtener-servicios-por-fecha", options={"expose": true})
     * @Method({"get"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesByDatesAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $qb = $manager->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->orderBy('r.startAt')
                ;

        if (null !== $request->query->get('q') && 1 === preg_match('/^(T|t)(?P<year>\d{1})(?P<month>\d{2})(?P<day>\d{2})(|-(?P<id>(\d{2}|\d{4})))$/', $request->query->get('q'), $matches)) {
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
            if (null !== $request->query->get('q')) {
                $qb
                        ->leftJoin('r.driver', 'd')
                        ->join('r.provider', 'p')
                        ;
                $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->andX(
                                $qb->expr()->isNotNull('r.driver'),
                                $qb->expr()->like('d.name', ':q')
                                ),
                        $qb->expr()->like('p.name', ':q')
                        ));
                $qb->setParameter('q', sprintf('%%%s%%', $request->query->get('q')));
            }

            if (!empty($from = $request->query->get('from'))) {
                $qb->andWhere($qb->expr()->gte('r.startAt', ':from'));
                $qb->setParameter('from', date_create_from_format('d/m/Y', $from)->format('Y-m-d'));
            }
            if (!empty($to = $request->query->get('to'))) {
                $qb->andWhere($qb->expr()->lte('r.startAt', ':to'));
                $qb->setParameter('to', date_create_from_format('d/m/Y', $to)->format('Y-m-d 23:59:59'));
            }
        }



        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb->getQuery(), $request->get('page', 1), 10);
        $vicheService = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        return new JsonResponse(array(
            'results' => array_map(function(Reserva $record) use($vicheService) {
                return array(
                    'id' => $record->getId(),
                    'text' => (string) $record,
                    'extra_data' => array(
                        'start_at' => $record->getStartAt()->format('d/m/Y H:i'),
                        'provider' => $record->getProvider()->getName(),
                        'service_name' => $record->getServiceType()->getName(),
                        'provider_image' => null !== $record->getProvider()->getLogoName() ? $vicheService->asset($record->getProvider(), 'logoFile') : null,
                        'driver_name' => $record->getDriver() !== null ? (string) $record->getDriver() : null
                    )
                );
            }, $pagination->getItems()),
            'pagination' => array(
                'more' => $pagination->getPageCount() !== (integer) $pagination->getPage()
            )
        ));
    }

    /**
     * @Route("/imprimir-modelo-programa/reserva-{id}", requirements={"id": "\d+"}, options={"expose": true})
     * @Method({"get", "post"})
     * @param Reserva $record
     * @return StreamedResponse
     */
    public function printProgramServiceModelAction(Reserva $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $phoneService = $this->container->get('libphonenumber.phone_number_util');
        $logoPath = $this->container->getParameter('kernel.root_dir').'/../web/uploads/logos';

        $report = new Reports\ProgramServiceModel($record, $phoneService, $manager, $logoPath);

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        }, 200, array('Content-Type' => 'application/pdf'));
    }
}
