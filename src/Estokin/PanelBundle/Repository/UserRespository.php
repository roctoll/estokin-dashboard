<?php

namespace Estokin\PanelBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserRepository extends EntityRepository implements UserProviderInterface {
	
	const CREATED = 0;
	const ACTIVE = 10;
	const INACTIVE = 20;
	/**
	 * loadUserByUsername.
	 *
	 * @param string $username
	 * @return Estokin\PanelBundle\Entity\User
	 */
	public function loadUserByUsername($username) {
		return $this->findOneBy(array('username' => $username));
	}
	
	function loadUser(UserInterface $user) {
		return $user;
	}
	
	function loadUserByAccount(AccountInterface $account) {
		return $this->loadUserByUsername($account->getUsername());
	}
	
	public function supportsClass($class) {
		return true;
	}
}
