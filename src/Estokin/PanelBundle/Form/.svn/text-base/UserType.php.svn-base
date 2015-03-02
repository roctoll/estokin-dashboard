<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('salt', 'hidden')
            ->add('password', 'password')
            ->add('email')
            ->add('isActive', 'hidden')
        ;
    }

    public function getName()
    {
        return 'estokin_panelbundle_usertype';
    }
}
