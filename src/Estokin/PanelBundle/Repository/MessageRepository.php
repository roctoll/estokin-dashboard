<?php

namespace Estokin\PanelBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageRepository extends EntityRepository
{
	
	
	/**
	 * Gets tickets by user.
	 *
	 * @param string $shop
	 *
	 * @return integer
	 */
	public function getTicketsShop($shop)
	{
		$q = $this->createQueryBuilder('p')
		->where('p.shop = :shop', 'p.type = :type')
		->setParameter('shop', $shop)
		->setParameter('type', "TICKET");
		//->setParameter('main', 'true');
		return $q->getQuery()->getResult();
	}
	
	/**
	 * Gets num new notifications by user.
	 *
	 * @param string $shop
	 *
	 * @return integer
	 */
	public function getNumNewNotifications($shop)
	{
		$q = $this->createQueryBuilder('p')
		->select('count(p.id)')
		->where('p.shop = :shop', 'p.admin = :admin', 'p.type = :type', 'p.is_read = :read')
		->setParameter('shop', $shop)
		->setParameter('admin', 1)
		->setParameter('type', "NOTIFICATION")
		->setParameter('read', 1);
		return current($q->getQuery()->getSingleResult());
	}
	
	/**
	 * Gets num new tickets by user.
	 *
	 * @param string $shop
	 *
	 * @return integer
	 */
	public function getNumNewTickets($shop)
	{
		$q = $this->createQueryBuilder('p')
		->select('count(p.id)')
		->where('p.shop = :shop', 'p.main = :main', 'p.type = :type', 'p.is_read = :read')
		->setParameter('shop', $shop)
		->setParameter('main', 1)
		->setParameter('type', "TICKET")
		->setParameter('read', 1);
		return current($q->getQuery()->getSingleResult());
	}
	/**
	 * Gets num new tickets.
	 *
	 * @param string $shop
	 *
	 * @return integer
	 */
	public function getNumNewTicketsA()
	{
		$q = $this->createQueryBuilder('p')
		->select('count(p.id)')
		->where('p.main = :main', 'p.type = :type', 'p.is_read = :read')
		->setParameter('main', 1)
		->setParameter('type', "TICKET")
		->setParameter('read', 2);
		return current($q->getQuery()->getSingleResult());
	}
}