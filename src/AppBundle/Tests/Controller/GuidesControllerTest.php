<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;
use AppBundle\Entity\Provider;
use AppBundle\Entity\Enterprise;
use AppBundle\Entity\TravelGuide;

class GuidesControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->clearTable('AppBundle:TravelGuide');
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/guias/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Guías")')->count());
    }

    public function testViewAction()
    {
        $enterprise = new Enterprise('Test enterprise');
        $this->em->persist($enterprise);

        for ($i = 0; $i < 2; $i++) {
            $provider = new Provider();
            $provider
                    ->setName(sprintf('Provider %s', $i + 1))
                    ->setEnterprise($enterprise)
                    ;

            $this->em->persist($provider);
        }

        $guide = new TravelGuide();
        $guide
                ->setName('Test guide')
                ->setContactInfo('Contact info')
                ->addProvider($provider)
                ->setEnterprise($enterprise)
                ;
        $this->em->persist($guide);
        $this->em->clear();

        $this->clearTable('AppBundle:TravelGuide');
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', sprintf('/guias/%s', $guide->getId()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1:contains("Guías")')->count());
    }
}
