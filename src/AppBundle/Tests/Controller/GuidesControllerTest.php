<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

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
}
