<?php

namespace Estokin\PanelBundle\Entity;

use Symfony\Component\Validator\Constraints\Date;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Estokin\PanelBundle\Entity\Order
 *
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\OrderRepository")
 * @ORM\Table(name="ekOrder")
 */
class Order
{
	/**
	 * Order states.
	 */
	const	
		STATE_SOLD	= 0,
		STATE_SENT	= 1,
		STATE_DONE	= 2,
		STATE_CANCELED = 3;
	
	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @var integer $PS_id
	 *
	 * @ORM\Column(name="PS_id", type="integer")
	 */
	private $PS_id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Shop", inversedBy="orders")
	 * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
	 */
	protected $shop;

	/**
	 * @ORM\ManyToOne(targetEntity="Item", inversedBy="orders")
	 * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
	 */
	private $item;
	
	/**
	 * @var integer $quantity
	 * 
	 * @ORM\Column(name="quantity", type="integer")
	 */
	private $quantity;
	
	/**
	 * @var date $date_sold
	 *
	 * @ORM\Column(name="date_sold", type="datetime")
	 */
	private $date_sold;
	
	/**
	 * @var date $date_upd
	 *
	 * @ORM\Column(name="date_upd", type="datetime", nullable=true)
	 */
	private $date_upd;
	
	/**
	 * @var string $state
	 *
	 * @ORM\Column(name="state", type="string", length=32)
	 */
	private $state;
	
	/**
	 * @var int $paid
	 *
	 * @ORM\Column(name="paid", type="integer")
	 */
	private $paid;
		
	/**
	 * Constructor.
	 */
	public function __construct($item, $quantity, $ps_id=-1)
	{
		$this->setItem($item);
		$this->setShop($item->getShop());
		$this->setState('STATE_SOLD');
		$this->setQuantity($quantity);
		$this->setPSId($ps_id);
		$this->setPaid(0);
		$this->setDateSold(new \DateTime());
		$this->setDateUpd(new \DateTime());
	}
	
	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Get PS_id
	 *
	 * @return integer
	 */
	public function getPSId()
	{
		return $this->id;
	}
	
	/**
	 * Set PS_id
	 *
	 * @param integer
	 */
	public function setPSId($id)
	{
		$this->PS_id = $id;
	}	
	
	/**
	 * Set shop
	 *
	 * @param Estokin\PanelBundle\Shop $shop
	 */
	public function setShop ($shop)
	{
		$this->shop = $shop;
	}
	
	/**
	 * Get shop
	 *
	 * @return Estokin\PanelBundle\Shop
	 */
	public function getShop()
	{
		return $this->shop;
	}
	
	/**
	 * Set product
	 *
	 * @param Estokin\PanelBundle\Item $item
	 */
	public function setItem ($item)
	{
		$this->item = $item;
	}
	
	/**
	 * Get item
	 *
	 * @return Estokin\PanelBundle\Item
	 */
	public function getItem()
	{
		return $this->item;
	}
	
	/**
	 * Set quantity
	 *
	 * @param integer $quantity
	 */
	public function setQuantity ($quantity)
	{
		$this->quantity = $quantity;
	}
	
	/**
	 * Get quantity
	 *
	 * @return integer
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	/**
	 * Set paid
	 *
	 * @param integer $paid
	 */
	public function setPaid($paid)
	{
		$this->paid = $paid;
	}
	
	/**
	 * Get paid
	 *
	 * @return integer
	 */
	public function getPaid()
	{
		return $this->paid;
	}
		
	/**
	 * Set state
	 *
	 * @param string $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}
	
	/**
	 * Get state
	 *
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}
	
	/**
	 * Set date_sold
	 *
	 * @param date $dateSold
	 */
	public function setDateSold($dateSold)
	{
		$this->date_sold = $dateSold;
	}
	
	/**
	 * Get date_sold
	 *
	 * @return date
	 */
	public function getDateSold()
	{
		return $this->date_sold;
	}
	
	/**
	 * Set date_upd
	 *
	 * @param date $dateUpd
	 */
	public function setDateUpd($dateUpd)
	{
		$this->date_upd = $dateUpd;
	}
	
	/**
	 * Get date_upd
	 *
	 * @return date
	 */
	public function getDateUpd()
	{
		return $this->date_upd;
	}
	
	/**
	* Get the order total amount 
	*
	* @return int
	*/
	public function getTotalAmount(){
		return $this->getItem()->getPrice() * $this->quantity;
	}
	
	public function __toString(){
    	return $this->item->getName()." [".$this->shop."]";
    }
    
	public function sellOrder(){
	
		$this->state = "STATE_SOLD";
		$this->date_upd = new \DateTime();
		return true;
	}	
	public function sendOrder(){

		$this->state = "STATE_SENT";
		$this->date_upd = new \DateTime();
		return true;
	}
	public function endOrder(){

		$this->state = "STATE_DONE";
		$this->date_upd = new \DateTime();
		return true;
	}
	public function cancelOrder(){
		$this->state = "STATE_CANCELED";
		$this->date_upd = new \DateTime();
		return true;
	}

}