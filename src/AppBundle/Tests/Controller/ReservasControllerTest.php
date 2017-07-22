<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

/**
 * Description of ReservasControllerTest
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservasControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->clearTable('AppBundle:Reserva');
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/reservas/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Reservas")')->count());
        $this->assertEquals(0, $crawler->filter('table#dataTables-drivers tbody tr')->count());
    }

    public function testNewAction()
    {
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/reservas/nueva');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Reservas")')->count());
        $this->assertEquals(1, $crawler->filter('form#reserva')->count());
    }
}
