<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Driver;

/**
 * Description of ReservaAssignedFilterType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservaAssignedFilterType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('choice', ChoiceType::class, array(
                    'choices' => array(
                        'Sí' => 'yes',
                        'No' => 'no',
                        'No y conductores' => 'with-drivers'
                    ),
                    'choices_as_values' => true,
                    'required' => false
                ))
                ->add('drivers', ChoiceType::class, array(
                    'multiple' => true,
                    'choices' => $this->getDriversChoices(),
                    'choices_as_values' => true,
                    'required' => false
                ))
                ;
    }

    private function getDriversChoices()
    {
        $query = $this->manager->createQuery('SELECT d.id, d.name FROM AppBundle:Driver AS d ORDER BY d.name');
        $result = $query->getResult();

        $drivers = array();
        foreach ($result as $driver) {
            $drivers[$driver['name']] = $driver['id'];
        }

        return $drivers;
    }
}
