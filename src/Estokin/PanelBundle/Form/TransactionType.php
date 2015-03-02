<?php

namespace Estokin\PanelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
        	->add('shop', 'entity', array(
            		'class' => 'EstokinPanelBundle:Shop',
            ))
            ->add('orders', 'entity', array(
            	'class' => 'EstokinPanelBundle:Order',
            	'query_builder' => function(\Estokin\PanelBundle\Repository\OrderRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.state = :state AND u.paid = :paid')
                        ->innerJoin('u.shop', 's')
                        ->setParameter('state', 'STATE_SOLD')
                        ->setParameter('paid', 0)
                        ->orderBy('s.name', 'ASC');
                        },
                'empty_value' => 'Choose an option',
           		'multiple' => true,
            ))
            ->add('comment');
    }

    public function getName()
    {
        return 'estokin_panelbundle_transactiontype';
    }
}
