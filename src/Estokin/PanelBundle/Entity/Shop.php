<?php 

namespace Estokin\PanelBundle\Entity;

use Estokin\PanelBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\Shop
 *
 * @ORM\Entity
 * 
 */
class Shop extends User {

	/**
	 * Shop states.
	 */
	const
	white 	= 0,
	silver 	= 1,
	red		= 2;
	
	/**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\Column(name="phone", type="string", length=32)
     */
    private $phone;
    
    /**
     * @ORM\Column(name="contact", type="string", length=255)
     */
    private $contact;
    
    /**
     * @ORM\Column(name="address", type="text")
     */
    private $address;
    
    /**
     * @ORM\Column(name="account", type="string")
     */
    private $account;
    
    /**
     * @ORM\Column(name="state", type="string", length=32)
     */
    private $state;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="shop")
     */
    private $messages;
    
    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="shop")
     */
    private $items;
    
    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="shop")
     */
    protected $orders;
    
    public function setName($name)
    {
    	$this->name = $name;
    }
    public function getName()
    {
    	return $this->name;
    }
    public function setPhone($phone)
    {
    	$this->phone = $phone;
    }
    public function getPhone()
    {
    	return $this->phone;
    }
    public function setContact($contact)
    {
    	$this->contact = $contact;
    }
    public function getContact()
    {
    	return $this->contact;
    }
    public function setAddress($address)
    {
    	$this->address = $address;
    }
    public function getAddress()
    {
    	return $this->address;
    }
    
    public function setAccount($account)
    {
    	$this->account = $account;
    }
    public function getAccount()
    {
    	return $this->account;
    }
    
    public function setState($state)
    {
    	$this->state = $state;
    }
    public function getState()
    {
    	return $this->state;
    }
    /**
     * Add items
     *
     * @param Estokin\PanelBundle\Item $items
     */
    public function addItems($items)
    {
    	$this->items[] = $items;
    }
    
    /**
     * Get items
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
    	return $this->items;
    }
    
    /**
     * Add orders
     *
     * @param Estokin\PanelBundle\Order $orders
     */
    public function addOrders($orders)
    {
    	$this->prodcuts[] = $orders;
    }
    
    /**
     * Get orders
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
    	return $this->orders;
    }
    
    /**
     * Add messages
     *
     * @param Estokin\PanelBundle\Message $messages
     */
    public function addMessages($messages)
    {
    	$this->messages[] = $messages;
    }
    
    /**
     * Get messages
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
    	return $this->messages;
    }
    
    public function getRoles()
    {
    	return array('ROLE_USER');
    }
    public function isShop(){
    	return 1;
    }
    
    public function __toString(){
    	return $this->name;
    }
    

}
