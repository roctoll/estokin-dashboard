<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ShopType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('username')
            ->add('email')
            ->add('phone', null, array('required'=>false))
            ->add('contact', null, array('required'=>false))
            ->add('address', null, array('required'=>false))
            ->add('account', null, array('required'=>false))
            ->add('state', 'choice', array(
				'choices' => array(
						'white'	=> 'Blanco',
						'silver' 		=> 'Gris',
						'red' 		=> 'Rojo'
						)
            	))
            ->add('salt', 'hidden')
            ->add('password')
            ->add('isActive')
        ;
    }

    public function getName()
    {
        return 'estokin_panelbundle_shoptype';
    }
}
