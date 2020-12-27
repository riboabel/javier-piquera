<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 12/20/2020
 * Time: 12:11 a.m.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\HostingInvoice;
use AppBundle\Entity\HostingInvoiceProvider;
use AppBundle\Form\Type\HostingInvoiceProviderFormType;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HostingInvoiceProvidersController
 *
 * @Route("/proveedores-factura-hospedaje")
 */
class HostingInvoiceProvidersController extends Controller
{
    /**
     * @Route("/", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $repository = $manager->getRepository('AppBundle:HostingInvoiceProvider');

        return $this->render('App/HostingInvoiceProviders/index.html.twig', ['providers' => $repository->findAll()]);
    }

    /**
     * @Route("/nuevo", methods={"GET", "POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $provider = new HostingInvoiceProvider();
        $provider->setNextAutoincrement(1);

        $form = $this->createForm(HostingInvoiceProviderFormType::class, $provider);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($provider);
            $manager->flush();

            $this->addFlash('notice', 'El proveedor se guardó.');

            return $this->redirectToRoute('app_hostinginvoiceproviders_index');
        }

        return $this->render('App/HostingInvoiceProviders/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/editar", methods={"GET", "POST"}, requirements={"id": "\d+"})
     * @param HostingInvoiceProvider $provider
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editAction(HostingInvoiceProvider $provider, Request $request)
    {
        $form = $this->createForm(HostingInvoiceProviderFormType::class, $provider);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($provider);
            $manager->flush();

            $this->addFlash('notice', 'El proveedor se guardó.');

            return $this->redirectToRoute('app_hostinginvoiceproviders_index');
        }

        return $this->render('App/HostingInvoiceProviders/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/eliminar", methods={"POST"}, requirements={"id": "\d+"})
     * @return RedirectResponse
     */
    public function deleteAction(HostingInvoiceProvider $provider)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($provider);
        $manager->flush();

        $this->addFlash('notice', 'El proveedor se eliminó.');

        return $this->redirectToRoute('app_hostinginvoiceproviders_index');
    }

    /**
     * @Route("/{id}/reset-autoincrement", methods={"POST"}, requirements={"id": "\d+"})
     * @param HostingInvoiceProvider $provider
     * @return JsonResponse
     */
    public function resetAutoincrementAction(HostingInvoiceProvider $provider, Request $request)
    {
        $response = [];

        if ($request->get('confirmed') == 'yes') {
            $provider
                ->setNextAutoincrement(1)
                ->setLastAutoincrementResetAt(new \DateTime('now'));
            $this->getDoctrine()->getManager()->flush();
            $response['action'] = 'redirect';
            $response['redirectUrl'] = $this->generateUrl('app_hostinginvoiceproviders_index');
            $this->addFlash('notice', 'El consecutivo se reinició.');
        } else {
            if (!$provider->getLastAutoincrementResetAt()) {
                $response['action'] = 'confirm';
                $response['message'] = '¿Seguro?';
            } else {
                $lastReset = Carbon::instance($provider->getLastAutoincrementResetAt());
                if ($lastReset->year == date('Y')) {
                    $response['action'] = 'confirm';
                    $response['message'] = sprintf('Ya reiniciaste este consecutivo una vez en este año en fecha %s. ¿Seguro que quieres volver a hacer esta operación?', $lastReset->format('d/m/Y'));
                }
            }
        }

        return new JsonResponse($response);
    }
}