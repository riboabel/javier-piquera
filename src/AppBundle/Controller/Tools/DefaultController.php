<?php

namespace AppBundle\Controller\Tools;

use AppBundle\Form\Type\ExportBookingsToExcelFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * DefaultController
 *
 * @Route("/herramientas")
 * @Security("has_role('ROLE_OWNER')")
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET", "POST"})
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(ExportBookingsToExcelFormType::class);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $leftDate = $form->get('left_date')->getData();
            $rightDate = $form->get('right_date')->getData();

            $exporter = $this->container->get('app.services.excel_exporter');

            $response = $exporter->export($leftDate, $rightDate);

            if (true === $form->get('delete_after_export')->getData()) {
                $this->getDoctrine()->getManager()
                    ->createQuery('DELETE FROM AppBundle:Reserva AS r WHERE r.startAt >= :left_date AND r.startAt <= :right_date')
                    ->setParameters(array(
                        'left_date' => $leftDate->format('Y-m-d'),
                        'right_date' => $rightDate->format('Y-m-d 23:59:59')
                    ))
                    ->execute();
            }

            $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, sprintf('exportacion desde %s hasta %s.xlsx', $leftDate->format('Y-m-d'), $rightDate->format('Y-m-d')));
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }

        return $this->render('App/Tools/Default/index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
