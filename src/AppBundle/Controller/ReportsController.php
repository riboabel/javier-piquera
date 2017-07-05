<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use AppBundle\Lib\Reports;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
                    'query_builder' => $manager->getRepository('AppBundle:ServiceType')
                            ->createQueryBuilder('st')
                            ->orderBy('st.name'),
                    'required' => false,
                    'label' => 'Servicios'
                ))
                ->add('includePlacesAddress', CheckboxType::class, array(
                    'label' => 'Incluir direcciones',
                    'required' => false
                ))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $report = new Reports\ServicesBetweenDatesReport($data['fromDate'], $data['toDate'],
                    $data['includePlacesAddress'], $data['services']->toArray(), $manager);

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
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AppBundle:Provider')->createQueryBuilder('p')
                ->orderBy('p.name');
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
                    'query_builder' => $qb
                ))
                ->add('services', EntityType::class, array(
                    'multiple' => true,
                    'class' => \AppBundle\Entity\ServiceType::class,
                    'query_builder' => $em->getRepository('AppBundle:ServiceType')
                            ->createQueryBuilder('st')
                            ->orderBy('st.name')
                ))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $report = new Reports\ServicesByProviderReport($data['fromDate'], $data['toDate'],
                    $data['provider'], $data['services']->toArray(), $em);

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
        $qb = $em->getRepository('AppBundle:Driver')
                ->createQueryBuilder('d')
                ->orderBy('d.name');
        $qb->where($qb->expr()->eq('d.enabled', $qb->expr()->literal(true)));

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
                    'class' => \AppBundle\Entity\Driver::class,
                    'label' => 'Conductor',
                    'query_builder' => $qb,
                    'multiple' => true
                ))
                ->add('includePlacesAddress', CheckboxTYpe::class, array(
                    'label' => 'Incluir direcciones',
                    'required' => false
                ))
                ->add('serviceType', EntityType::class, array(
                    'class' => \AppBundle\Entity\ServiceType::class,
                    'label' => 'Servicio',
                    'query_builder' => $em->getRepository('AppBundle:ServiceType')
                            ->createQueryBuilder('s')
                            ->orderBy('s.name'),
                    'required' => false,
                    'multiple' => true
                ))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $report = new Reports\ServicesByDriverReport($form->getData(), $em);

                return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
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
            'prices' => $request->get('prices', array())
        ), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/modelo-de-cobro", name="app_reports_charge_report")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function printChargeReportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\ChargeForm(array(
            'ids' => $request->get('ids', array()),
            'prices' => $request->get('prices', array())
        ), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/imprimir-factura/{invoiceNumber}", requirements={"invoiceNumber": "\d{4}\/\d{4}"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param \AppBundle\Entity\Reserva $record
     * @return Response
     */
    public function printInvoiceAction(\AppBundle\Entity\Reserva $record)
    {
        $em = $this->getDoctrine()->getManager();

        $report = new Reports\Invoice(array(
            'record' => $record,
            'logo_path' => $this->container->getParameter('kernel.root_dir').'/../web/uploads/logos'
        ), $em);

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/imprimir-orden-de-trabajo/{id}", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
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

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
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

        return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
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

            return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
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

            return new Response($report->getContent(), 200, array('Content-Type' => 'application/pdf'));
        }

        return $this->render('App/Reports/form_old_pays.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
