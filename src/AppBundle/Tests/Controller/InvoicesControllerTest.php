<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

/**
 * InvoicesControllerTest
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class InvoicesControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->clearTable('AppBundle:Invoice');
        $this->signIn('admin', 'admin');

        $crawler = $this->client->request('GET', '/facturas/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1.page-header:contains("Facturas")')->count());
    }
}
