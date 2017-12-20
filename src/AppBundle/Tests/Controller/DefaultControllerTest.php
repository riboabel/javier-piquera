<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Lib\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->clearTable('AppBundle:User');
        $this->createUser('admin', 'admin', array('ROLE_ADMIN'));
        $this->signIn('admin', 'admin');

        $this->client->request('GET', '/');

        $this->assertTrue($this->client->getResponse()->isRedirect('/controles/'));
    }
}