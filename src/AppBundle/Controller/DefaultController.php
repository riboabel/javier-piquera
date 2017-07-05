<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Description of DefaultController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method({"get"})
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('app_dashboard_index'));
    }

    /**
     * @return Response
     */
    public function dropdownNotificationsAction()
    {
        if (!in_array(date('w'), array(1))) {
            return new Response();
        }

        return $this->render('App/Default/_dropdown_notifications.html.twig');
    }

    /**
     * @Route("/tasks-dropdown-content", options={"expose": true})
     * @Method({"get"})
     * @return Response
     */
    public function getTasksDropdownContentAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $startAt = new \DateTime('next tuesday');
        $endAt = new \DateTime(\date('Y-m-d H:i:s', \strtotime('+6 days', $startAt->format('U'))));
        $thisWeek = \date('W');

        $providers = $manager->createQuery('SELECT p.id, p.name FROM AppBundle:Reserva AS r JOIN r.provider AS p WHERE r.startAt >= :startAt AND r.startAt <= :endAt AND r.isCancelled = :no GROUP BY p.name, p.id')
                ->setParameters(array(
                    'startAt' => $startAt->format('Y-m-d 00:00:00'),
                    'endAt' => $endAt->format('Y-m-d 23:59:59'),
                    'no' => false
                ))
                ->getResult()
                ;
        $counter = 0; $total = count($providers);
        foreach ($providers as $p) {
            $record = $manager->getRepository('AppBundle:WeekConceal')->findOneBy(array(
                'week' => $thisWeek,
                'year' => date('Y'),
                'provider' => $p['id']
            ));

            if ($record) {
                $counter++;
            }
        }

        return $this->render('App/Default/tasks_dropdown_content.html.twig', array(
            'percent' => $total === 0 ? 1 : ($counter / $total)
        ));
    }

    /**
     * @Route("/week-pdfs")
     * @Method({"get"})
     * @return Response
     */
    public function listWeekPdfsAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $startAt = new \DateTime('next tuesday');
        $endAt = new \DateTime(\date('Y-m-d H:i:s', strtotime('+6 days', $startAt->format('U'))));
        $thisWeek = date('W');
        $thisYear = date('Y');

        $providers = $manager->createQuery('SELECT p.id, p.name FROM AppBundle:Reserva AS r JOIN r.provider AS p WHERE r.startAt >= :startAt AND r.startAt <= :endAt AND r.isCancelled = :no GROUP BY p.name, p.id')
                ->setParameters(array(
                    'startAt' => $startAt->format('Y-m-d 00:00:00'),
                    'endAt' => $endAt->format('Y-m-d 23:59:59'),
                    'no' => false
                ))
                ->getResult()
                ;
        $counter = 0; $total = count($providers);
        foreach ($providers as $i => $p) {
            $record = $manager->getRepository('AppBundle:WeekConceal')->findOneBy(array(
                'week' => $thisWeek,
                'year' => date('Y'),
                'provider' => $p['id']
            ));

            $providers[$i]['p'] = $record;
        }

        return $this->render('App/Default/list_week_pdfs.html.twig', array(
            'startAt' => $startAt,
            'endAt' => $endAt,
            'providers' => $providers
        ));
    }

    /**
     * @Route("/generate-week_conceal-report/{id}", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("provider", class="AppBundle\Entity\Provider")
     * @param \AppBundle\Entity\Provider $provider
     * @return StreamedResponse
     */
    public function generateWeekConcealReportAction(\AppBundle\Entity\Provider $provider)
    {
        $manager = $this->getDoctrine()->getManager();
        $startAt = new \DateTime('next tuesday');
        $endAt = new \DateTime(\date('Y-m-d H:i:s', \strtotime('+6 days', $startAt->format('U'))));
        $thisWeek = \date('W');
        $thisYear = \date('Y');

        $record = $manager->getRepository('AppBundle:WeekConceal')->findOneBy(array(
            'week' => $thisWeek,
            'year' => $thisYear,
            'provider' => $provider->getId()
        ));
        if (!$record) {
            $record = new \AppBundle\Entity\WeekConceal();
            $record
                    ->setProvider($provider)
                    ->setWeek($thisWeek)
                    ->setYear($thisYear)
                    ;
            $manager->persist($record);
            $manager->flush();
        }

        $report = new \AppBundle\Lib\Reports\ConcealReport($startAt, $endAt, $provider, array(), $manager);

        return new StreamedResponse(function() use($report) {
            file_put_contents('php://output', $report->getContent());
        });
    }
}
