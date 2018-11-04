<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ReservaTercero;
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
     * @return Response
     */
    public function alertsDropdownAction()
    {
        $fifteenDaysForward = new \DateTime('+15 days');

        $manager = $this->getDoctrine()->getManager();
        $query = $manager->createQuery('SELECT COUNT(r) FROM AppBundle:Reserva AS r WHERE r.startAt <= :date15 AND r.startAt >= :today AND r.isExecuted = false AND r.isCancelled = false AND r.hasIncompleteData = true')
            ->setParameters(array(
                'date15' => $fifteenDaysForward->format('Y-m-d'),
                'today' => date('Y-m-d H:i:s')
            ));
        $result = $query->getResult();

        return $this->render('App/Default/_alerts_dropdown.html.twig', array(
            'alerts' => $result[0][1]
        ));
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

        $ordinaryProviders = $manager->createQuery('SELECT p.id FROM AppBundle:Reserva AS r JOIN r.provider AS p WHERE r.startAt >= :startAt AND r.startAt <= :endAt AND r.isCancelled = :no GROUP BY p.id')
                ->setParameters(array(
                    'startAt' => $startAt->format('Y-m-d 00:00:00'),
                    'endAt' => $endAt->format('Y-m-d 23:59:59'),
                    'no' => false
                ))
                ->getResult()
                ;
        $thirdProviders = $manager->createQuery('SELECT c.id FROM AppBundle:ReservaTercero AS r JOIN r.client AS c WHERE r.type = :clasicos AND r.startAt >= :startAt AND r.startAt <= :endAt AND r.state = :statusCreated GROUP BY c.id')
            ->setParameters(array(
                'clasicos' => ReservaTercero::TYPE_CLASICOS,
                'startAt' => $startAt->format('Y-m-d 00:00:00'),
                'endAt' => $endAt->format('Y-m-d 23:59:59'),
                'statusCreated' => ReservaTercero::STATE_CREATED
            ))
            ->getResult();
        $sumProviders = array_merge(array_map(
            function($p) {
                return $p['id'];
            },
            $ordinaryProviders
        ), array_map(
            function($p) {
                return $p['id'];
            },
            $thirdProviders
        ));

        $providers = $manager->createQuery('SELECT p.id, p.name FROM AppBundle:Provider AS p WHERE p.id IN (:ids) ORDER BY p.name')
            ->setParameter('ids', $sumProviders)
            ->getResult();

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

        $ordinaryProviders = $manager->createQuery('SELECT p.id FROM AppBundle:Reserva AS r JOIN r.provider AS p WHERE r.startAt >= :startAt AND r.startAt <= :endAt AND r.isCancelled = :no GROUP BY p.id')
            ->setParameters(array(
                'startAt' => $startAt->format('Y-m-d 00:00:00'),
                'endAt' => $endAt->format('Y-m-d 23:59:59'),
                'no' => false
            ))
            ->getResult()
        ;
        $thirdProviders = $manager->createQuery('SELECT c.id FROM AppBundle:ReservaTercero AS r JOIN r.client AS c WHERE r.type = :clasicos AND r.startAt >= :startAt AND r.startAt <= :endAt AND r.state = :statusCreated GROUP BY c.id')
            ->setParameters(array(
                'clasicos' => ReservaTercero::TYPE_CLASICOS,
                'startAt' => $startAt->format('Y-m-d 00:00:00'),
                'endAt' => $endAt->format('Y-m-d 23:59:59'),
                'statusCreated' => ReservaTercero::STATE_CREATED
            ))
            ->getResult();
        $sumProviders = array_merge(array_map(
            function($p) {
                return $p['id'];
            },
            $ordinaryProviders
        ), array_map(
            function($p) {
                return $p['id'];
            },
            $thirdProviders
        ));

        $providers = $manager->createQuery('SELECT p.id, p.name FROM AppBundle:Provider AS p WHERE p.id IN (:ids) ORDER BY p.name')
            ->setParameter('ids', $sumProviders)
            ->getResult();

        foreach ($providers as $i => $p) {
            $record = $manager->getRepository('AppBundle:WeekConceal')->findOneBy(array(
                'week' => $thisWeek,
                'year' => $thisYear,
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
     * @Route("/generate-week-conceal-report/{id}", requirements={"id": "\d+"})
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
