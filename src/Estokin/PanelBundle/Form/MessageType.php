<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MessageType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('subject', 'text')
		->add('content', 'textarea')
		->add('shop', 'entity', array(
				'class' => 'EstokinPanelBundle:Shop',
				))
		->add('type', 'choice', array(
				'choices' => array(
						'NOTIFICATION'	=> 'Notificación',
						'TICKET' 		=> 'Ticket'
						),
		))
		->add('priority', 'choice', array(
        			'choices' => array(
        					'1'	=> 'Alta',
        					'2' => 'Media',
        					'3'	=> 'Baja',),
        			));
	}

	public function getName()
	{
		return 'estokin_panelbundle_messagetype';
	}
	public function getDefaultOptions(array $options)
	{
		return array(
				'data_class' => 'Estokin\PanelBundle\Entity\Message',
				'csrf_protection' => false,
		);
	}
}