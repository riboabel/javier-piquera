<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\ThirdProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * UniqueThirdProviderSerialPrefixValidator
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class UniqueThirdProviderSerialPrefixValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * UniqueThirdProviderSerialPrefixValidator constructor.
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ThirdProvider $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value->getIsSerialGenerator()) {
            return;
        }

        $record = $this->findRecord($value->getId(), $value->getSerialPrefix());

        if ($record) {
            $this->context->buildViolation($constraint->message)
                ->atPath('serialPrefix')
                ->addViolation();
        }
    }

    /**
     * @param integer   $id
     * @param string    $prefix
     * @return array
     */
    private function findRecord($id, $prefix)
    {
        $qb = $this->manager->getRepository('AppBundle:ThirdProvider')
            ->createQueryBuilder('p')
            ->where('p.isSerialGenerator = true AND p.serialPrefix = :prefix')
            ->setParameter('prefix', $prefix);

        if (null !== $id) {
            $qb->andWhere('p.id <> :id')
                ->setParameter('id', $id);
        }

        return $qb->getQuery()->setMaxResults(1)->getResult();
    }
}
