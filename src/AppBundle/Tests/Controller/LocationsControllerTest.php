<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

/**
 * LocationsControllerTest
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class LocationsControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->clearTable('AppBundle:Location');
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/localidades/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Localidades")')->count());
    }
}
