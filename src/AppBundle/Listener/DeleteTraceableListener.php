<?php

namespace AppBundle\Listener;

use AppBundle\Entity\Trace;
use AppBundle\Entity\User;
use AppBundle\Model\DeleteTraceableInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * DeleteTraceableListener
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class DeleteTraceableListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!($entity instanceof DeleteTraceableInterface)) {
            return;
        }

        $this->createTrace($args);
    }

    private function createTrace(LifecycleEventArgs $args)
    {
        $manager = $args->getEntityManager();
        $entity = $args->getEntity();

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        /** @var User $user */
        $user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;

        $trace = new Trace();
        $trace
            ->setType(Trace::TYPE_REMOVAL)
            ->setData($entity->getDeleteTraceableData())
            ->setCreatedBy($user ? $user->getFullName() : 'cli');

        $manager->persist($trace);
    }
}
