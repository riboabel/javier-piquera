<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

class DriversControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->clearTable('AppBundle:Driver');
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/conductores/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Conductores")')->count());
    }

    public function testViewAction()
    {
        $this->clearTable('AppBundle:Driver');
        $driver = $this->createDriver('Test driver', null, true);

        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', sprintf('/conductores/%s/ver', $driver->getId()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Conductores")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Test driver")')->count());
    }

    public function testNewAction()
    {
        $this->clearTable('AppBundle:Driver');
        $driver = $this->createDriver('Test driver', null, true);

        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/conductores/nuevo');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Conductores")')->count());
        $this->assertEquals(1, $crawler->filter('form#driver')->count());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->clearTable('AppBundle:User');
        $this->createUser('admin', 'admin', array('ROLE_ADMIN'));
    }

    protected function createDriver($name, $contactInfo, $isDriverGuide, $flush = true)
    {
        $enterprise = $this->em->getRepository('AppBundle:Enterprise')->findOneBy(array());
        $driver = new \AppBundle\Entity\Driver();
        $driver
                ->setName($name)
                ->setContactInfo($contactInfo)
                ->setIsDriverGuide($isDriverGuide)
                ->setEnterprise($enterprise);

        $this->em->persist($driver);
        if ($flush) {
            $this->em->flush();
        }

        return $driver;
    }
}
