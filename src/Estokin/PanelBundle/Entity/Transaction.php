<?php

namespace Estokin\PanelBundle\Entity;

use Symfony\Component\Validator\Constraints\Date;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Estokin\PanelBundle\Entity\Transaction
 *
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\TransactionRepository")
 * @ORM\Table(name="Transaction")
 */
class Transaction
{
	/**
	 * Transaction states.
	 */
	const	
		STATE_PENDENT	= 0,
		STATE_DONE	= 1,
		STATE_CANCELED = 2;
	
	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Shop", inversedBy="Transactions")
	 * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
	 */
	protected $shop;

	/**
     * @ORM\ManyToMany(targetEntity="Order")
     * @ORM\JoinTable(name="transaction_orders",
     *      joinColumns={@ORM\JoinColumn(name="transaction_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id", unique=true)}
     *      )
     **/
	private $orders;
	
	/**
     * @ORM\ManyToMany(targetEntity="Entry")
     * @ORM\JoinTable(name="transaction_entries",
     *      joinColumns={@ORM\JoinColumn(name="transaction_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="entry_id", referencedColumnName="id", unique=true)}
     *      )
     **/
	private $entries;
	
	/**
	 * @var integer $amount
	 * 
	 * @ORM\Column(name="amount", type="integer")
	 */
	private $amount;
	
	/**
	 * @var date $date_add
	 *
	 * @ORM\Column(name="date_add", type="datetime")
	 */
	private $date_add;
	
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
	 * @var string $comment
	 *
	 * @ORM\Column(name="comment", type="text")
	 */
	private $comment;
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->setState('STATE_PENDENT');
		$this->setDateAdd(new \DateTime());
		$this->setDateUpd(new \DateTime());
		$this->setComment('Pago semanal');
		$this->setAmount();
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
     * Add Order
     *
     * @param Estokin\PanelBundle\Order $order
     */
    public function addOrder($order)
    {
        $this->orders[] = $order;
        $this->amount = $this->amount + $order->getTotalAmount();
    }
    /**
	 * Set orderS
	 *
	 * @param Doctrine\Common\Collections\Collection
	 */
	public function setOrders ($orders)
	{ 
		$this->orders = $orders;
		$this->setAmount();
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
     * Add entry
     *
     * @param Estokin\PanelBundle\Entry $entry
     */
    public function addEntry($entry)
    {
        $this->entries[] = $entry;
        $this->amount = $this->amount + $entry->getValue();
    }
    /**
	 * Set entries
	 *
	 * @param Doctrine\Common\Collections\Collection
	 */
	public function setEntries ($entries)
	{ 
		$this->entries = $entries;
		$this->setAmount();
	}


    /**
     * Get entries
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }
	
	/**
	 * Set amount
	 *
	 * @param integer $amount
	 */
	public function setAmount ()
	{
		$amount = 0;
		if($this->orders){			
			foreach($this->orders as $order){
				$amount = $amount + $order->getTotalAmount();
			}
		}
		if($this->entries){
			foreach($this->entries as $entry){
				$amount = $amount + $entry->getValue();
			}
		}
		$this->amount = $amount;
	}
	
	/**
	 * Get amount
	 *
	 * @return integer
	 */
	public function getAmount()
	{
		return $this->amount;
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
     * Set comment
     *
     * @param text $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return text 
     */
    public function getComment()
    {
        return $this->comment;
    }
	
	/**
	 * Set date_add
	 *
	 * @param date $dateadd
	 */
	public function setDateadd($dateadd)
	{
		$this->date_add = $dateadd;
	}
	
	/**
	 * Get date_add
	 *
	 * @return date
	 */
	public function getDateadd()
	{
		return $this->date_add;
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
	
	public function __toString(){
    	return $this->id;
    }

	public function restoreTransaction(){

		$this->state = "STATE_PENDENT";
		$this->date_upd = new \DateTime();
		return true;
	}
	public function endTransaction(){
		
		if($this->state != "STATE_PENDENT") return false;
		
		$this->state = "STATE_DONE";
		$this->date_upd = new \DateTime();
		return true;
	}
	public function cancelTransaction(){
		$this->state = "STATE_CANCELED";
		$this->date_upd = new \DateTime();
		return true;
	}

}