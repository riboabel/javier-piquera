<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ReservaTercero;
use AppBundle\Entity\ThirdProvider;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Lib\Reports;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ThirdProviderReportsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/informes-terceros")
 */
class ThirdProviderReportsController extends Controller
{
    /**
     * @Route("/reservas-{type}", requirements={"type": "clasicos|microbus|guia"})
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function reservasAction(Request $request, $type)
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
            ->add('provider', EntityType::class, array(
                'class' => ThirdProvider::class,
                'label' => 'Proveedor',
                'query_builder' => function(EntityRepository $repository) use($type) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.type = :type')
                        ->orderBy('p.name')
                        ->setParameter('type', $type);
                },
                'required' => true
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $report = new Reports\ServicesForThirdProviderReport($form->getData(), $manager, $this->container->getParameter('vich_uploader.mappings')['logos']['upload_destination'], $type);

                return new StreamedResponse(function() use($report) {
                    file_put_contents('php://output', $report->getContent());
                }, 200, array('Content-Type' => 'application/pdf'));
            }
        }

        return $this->render('App/Reports/third_provider_report_filter.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
