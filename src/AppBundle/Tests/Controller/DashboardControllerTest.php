<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->clearTable('AppBundle:User');
        $this->createUser('admin', 'admin', array('ROLE_ADMIN'));
    }

    public function testIndexAction()
    {
        $this->signIn('admin', 'admin');

        $this->client->request('GET', '/controles/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}