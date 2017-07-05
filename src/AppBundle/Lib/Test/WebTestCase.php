<?php

namespace AppBundle\Lib\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseClass;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
/**
 * Description of WebTestCase
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class WebTestCase extends BaseClass
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Client
     */
    protected $client;


    protected function setUp()
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function createUser($username, $password, $roles = array())
    {
        $this->em->createQuery('DELETE FROM AppBundle:Enterprise')->execute();
        $enterprise = new \AppBundle\Entity\Enterprise('Test enterprise');
        $this->em->persist($enterprise);
        $this->em->flush();

        $userManager = $this->client->getContainer()->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user
                ->setUsername($username)
                ->setPlainPassword($password)
                ->setEnabled(true)
                ->setRoles($roles)
                ->addEnterprise($enterprise)
                ;

        $userManager->updateUser($user);

        return $user;
    }

    protected function signIn($username = 'admin', $password = 'admin')
    {
        $this->client->restart();
        $crawler = $this->client->request('GET', '/ingreso');
        $form = $crawler->selectButton('Ingresar')->form();
        $this->client->submit($form, array(
            '_username' => $username,
            '_password' => $password
        ));
    }

    protected function clearTable($className)
    {
        $this->em->createQuery(sprintf('DELETE FROM %s', $className))->execute();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}
