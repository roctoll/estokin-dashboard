<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
	        ->add('file')
	        ->getForm(),
    }

    public function getName()
    {
        return 'estokin_panelbundle_documenttype';
    }

}
