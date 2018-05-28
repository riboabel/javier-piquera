<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ExportBookingsToExcelFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ExportBookingsToExcelFormType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('left_date', DateType::class, array(
                'data' => $this->getLeftDate(),
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'html5' => false,
                'required' => true
            ))
            ->add('right_date', DateType::class, array(
                'data' => $this->getRightDate(),
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'html5' => false,
                'required' => true
            ))
            ->add('delete_after_export', ICheckType::class, array(
                'required' => false
            ));
    }

    private function getLeftDate()
    {
        $dql = $this->entityManager->createQuery('SELECT MIN(r.startAt) FROM AppBundle:Reserva AS r');
        $result = $dql->getResult();

        return new \DateTime($result[0][1]);
    }

    private function getRightDate()
    {
        $dql = $this->entityManager->createQuery('SELECT MAX(r.startAt) FROM AppBundle:Reserva AS r');
        $result = $dql->getResult();

        return new \DateTime($result[0][1]);
    }
}
