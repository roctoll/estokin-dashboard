<?php

namespace Estokin\PanelBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TransactionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TransactionRepository extends EntityRepository
{

	/**
	 * Gets Total revenue.
	 *
	 * @return integer
	 */
	public function getTotalCashA()
	{
		$q = $this->createQueryBuilder('p')
		->select('SUM(p.amount)')
		->where('p.state != :state')
		->setParameter('state', 'STATE_CANCELED');
		return current($q->getQuery()->getSingleResult()); 
	}
	/**
	 * Gets Total revenue by user.
	 *
	 * @param string $shop
	 *
	 * @return integer
	 */
	public function getTotalCash($shop)
	{
		$q = $this->createQueryBuilder('p')
		->select('SUM(p.amount)')
		->where('p.shop = :shop', 'p.state != :state')
		->setParameter('shop', $shop)
		->setParameter('state', 'STATE_CANCELED');
		return current($q->getQuery()->getSingleResult()); 
	}
	
}