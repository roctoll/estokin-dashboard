<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('PS_id')
            ->add('reference')
            ->add('shopreference')
            ->add('state')
            ->add('stock')
            ->add('pvp')
            ->add('price')
            ->add('product', 'entity', array(
            		'class' => 'EstokinPanelBundle:Product',
            ))
            ->add('shop', 'entity', array(
            		'class' => 'EstokinPanelBundle:Shop',
            ))
            ->add('shopreference')
	        ->add('attr', 'entity', array(
			        'multiple'=>'true',
	        		'class' => 'EstokinPanelBundle:Attr',
	        ))
	        ->add('comment');
    }

    public function getName()
    {
        return 'estokin_panelbundle_itemtype';
    }
}
