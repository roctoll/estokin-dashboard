<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('level')
            ->add('PS_id')
            ->add('parent')
            ->add('categories');
    }

    public function getName()
    {
        return 'estokin_panelbundle_categorytype';
    }
}
