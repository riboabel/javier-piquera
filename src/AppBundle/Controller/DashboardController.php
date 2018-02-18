<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ReservaTercero;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Description of DashboardController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/controles")
 */
class DashboardController extends Controller
{
    /**
     * @Route("/", name="app_dashboard_index")
     * @Method({"get"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $tomorrow = new \DateTime(date_create('now')->add(new \DateInterval('P1D'))->format('Y-m-d 00:00:00'));

        return $this->render('App/Dashboard/index.html.twig', array(
            'incompleteReservasForTomorrow' => $em->getRepository('AppBundle:Reserva')
                ->countNotReadyReservasForDate($tomorrow),
            'readyReservasForTomorrow' => $em->getRepository('AppBundle:Reserva')
                ->countReadyBetweenDates($tomorrow, $tomorrow),
            'notExecutedReservas' => $em->getRepository('AppBundle:Reserva')
                ->countReadyBetweenDates(null, date_create('now')->sub(new \DateInterval('P1D'))),
            'totalReservas' => $em->getRepository('AppBundle:Reserva')->countAll(),
            'morrisAreaData' => $this->container->get('app.morris_graph.area')->getData(),
            'total_clasicos' => $em->getRepository('AppBundle:ReservaTercero')->countByType(ReservaTercero::TYPE_CLASICOS),
            'total_microbus' => $em->getRepository('AppBundle:ReservaTercero')->countByType(ReservaTercero::TYPE_MICROBUS)
        ));
    }
}
