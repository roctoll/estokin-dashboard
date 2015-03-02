<?php

namespace Estokin\PanelBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\User
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"user" = "User", "shop" = "Shop"})
 */
class User implements UserInterface, \Serializable {
	/**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="username", type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(name="salt", type="string", length=40)
     */
    private $salt;

    /**
     * @ORM\Column(name="password", type="string", length=40)
     */
    private $password;

    /**
     * @ORM\Column(name="email", type="string", length=60)
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;
    

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    public function getRoles()
    {
        return array('ROLE_ADMIN');
    }

    public function equals(UserInterface $user)
    {
        return $user->getUsername() === $this->username;
    }

    public function eraseCredentials()
    {
    }
    
	public function setUsername($username)
    {
        $this->username = $username;
    }
    public function getUsername()
    {
        return $this->username;
    }

	public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return $this->id;
    }
    
	public function setSalt($salt)
    {
        $this->salt = $salt;
    }
    public function getSalt()
    {
        return $this->salt;
    }

	public function setPassword($password)
    {
        $this->password = $password;
    }
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function getEmail()
    {
        return $this->email;
    }
    
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }
    public function getIsActive()
    {
        return $this->isActive;
    }
    public function isShop(){
    	return 0;
    }
    
    public function serialize()
    {
    	return serialize($this->id);
    }
    
    public function unserialize($data)
    {
    	$this->id = unserialize($data);
    }
}
