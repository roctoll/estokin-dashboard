<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Estokin\PanelBundle\Entity\Tag;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
			->add('brand', 'entity', array(
				'class' => 'EstokinPanelBundle:Brand',
				))
            ->add('code', null, array('required'=>false))
            ->add('season', 'choice', array(
        		'choices' => array(
        			null => '',
        			2006 => 2006,
        			2007 => 2007,
        			2008 => 2008,
        			2009 => 2009,
        			2010 => 2010,
        			2011 => 2011,
        			2012 => 2012,
        			2013 => 2013,
        		)))
            ->add('category')
            ->add('description', null, array('required'=>false))
            ->add('comment', null, array('required'=>false))
            ->add('image', 'file', array('required'=>false))
        	->add('state', 'choice', array(
				'choices' => array(
					'STATE_RAW'		=> 'RAW',
					'STATE_VALID' 	=> 'VALID',
					'STATE_ONLINE' 	=> 'ONLINE',
					'STATE_BLOCK' 	=> 'BLOCK',
        		),
        	));
    }

    public function getName()
    {
        return 'estokin_panelbundle_producttype';
    }
    //@TODO CSRF token de Deu
    /**
     * Returns the default options for this type.
     * 
     * Every form needs to know the name of the class that holds the underlying
     * data. Usually, this is just guessed based off of the object passed to 
     * the second argument to createForm. Later, when you begin embedding 
     * forms, this will no longer be sufficient. So, while not always 
     * necessary, it's generally a good idea to explicitly specify the 
     * data_class option by add the following to your form type class.
     *  
     * @param array $options
     * 
     * @return array The default options
     */
    public function getDefaultOptions(array $options)
    {
        return array(
        		'data_class' => 'Estokin\PanelBundle\Entity\Product',
        		'csrf_protection' => false,
        );
    }
}

