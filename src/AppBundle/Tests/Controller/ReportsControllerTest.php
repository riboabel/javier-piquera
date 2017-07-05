<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

class ReportsControllerTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->clearTable('AppBundle:User');
        $this->createUser('admin', 'admin', array('ROLE_ADMIN'));
    }

    public function testReservasBetweenDatesActionA()
    {
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/informes/reservas-entre-fechas');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('form')->count());
    }

    /**
     * @depends testReservasBetweenDatesActionA
     */
    public function testReservasBetweenDatesActionB()
    {
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/informes/reservas-entre-fechas');
        $form = $crawler->filter('form')->form();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testReservasByProviderActionA()
    {
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/informes/reservas-por-agencia');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('form')->count());
    }

    /**
     * @depends testReservasByProviderActionA
     */
    public function testReservasByProviderActionB()
    {
        $this->clearTable('AppBundle:Provider');
        $this->em->persist($provider = new \AppBundle\Entity\Provider());
        $provider
                ->setName('Test provider')
                ->setContactInfo('address and phone')
                ->setEnterprise($this->getEnterprise());
        $this->em->flush();
        $this->em->clear();

        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/informes/reservas-por-agencia');
        $form = $crawler->filter('form')->form();

        $this->client->submit($form, array(
            'form[provider]' => $this->em->getRepository('AppBundle:Provider')->findOneBy(array())->getId()
        ));

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testReservasByDriverActionA()
    {
        $this->clearTable('AppBundle:Driver');
        $this->em->persist($driver = new \AppBundle\Entity\Driver());
        $driver
                ->setName('Test driver')
                ->setContactInfo('address and phone')
                ->setEnterprise($this->getEnterprise());
        $this->em->flush();
        $this->em->clear();

        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/informes/reservas-por-conductor');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('form')->count());
    }

    /**
     * @depends testReservasByDriverActionA
     */
    public function testReservasByDriverActionB()
    {
        $this->clearTable('AppBundle:Driver');
        $this->em->persist($driver = new \AppBundle\Entity\Driver());
        $driver
                ->setName('Test driver')
                ->setContactInfo('address and phone')
                ->setEnterprise($this->getEnterprise());
        $this->em->flush();
        $this->em->clear();

        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/informes/reservas-por-conductor');

        $form = $crawler->filter('form')->form();
        $this->client->submit($form, array(
            'form[drivers]' => array($this->em->getRepository('AppBundle:Driver')->findOneBy(array())->getId())
        ));

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testPrintSelectionAction()
    {
        $this->signIn('admin', 'admin');

        $this->client->request('POST', '/informes/reservas-seleccionadas');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-Type'));
    }

    /**
     * @return \AppBundle\Entity\Enterprise
     */
    private function getEnterprise()
    {
        return $this->em->getRepository('AppBundle:Enterprise')->findOneBy(array());
    }
}
