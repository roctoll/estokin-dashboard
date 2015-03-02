<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AttrType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('value')
            ->add('PS_id')
            ->add('attrSet', 'entity', array(
            		'class' => 'EstokinPanelBundle:AttrSet',
            ))
        ;
    }

    public function getName()
    {
        return 'estokin_panelbundle_attrtype';
    }
}
