<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\DataTransformer\ArrayToRoleTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\User;
use AppBundle\Form\Type\RolesType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Description of UserType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class UserType extends AbstractType
{
    /**
     * @var boolean
     */
    private $showEnterprise;

    public function __construct(TokenStorage $storage)
    {
        $this->showEnterprise = $storage->getToken()->getUser()->hasRole('ROLE_ADMIN');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName')
            ->add('username')
            ->add('imageFile', VichImageType::class, array(
                'required' => false
            ))
            ->add($builder->create('roles', RolesType::class)->addModelTransformer(new ArrayToRoleTransformer()))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $form->add('plainPassword', RepeatedType::class, array(
                    'required'  => null === $data->getId(),
                    'type'      => PasswordType::class
                ));
            });

        if ($this->showEnterprise) {
            $builder->add('enterprises');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class
        ));
    }
}
