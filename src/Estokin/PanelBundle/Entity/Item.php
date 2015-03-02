<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\Item
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\ItemRepository")
 */
class Item
{
	/**
     * Product states.
     */
    const
        STATE_RAW		= 0,
        STATE_ONLINE	= 1,
        STATE_DEAD		= 2;

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
     * @ORM\Column(name="PS_id", type="integer", nullable="true")
     */
    private $PS_id;
        
    /**
     * @var string $reference
     *
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;
        
    /**
     * @var string $shop_reference
     *
     * @ORM\Column(name="shopreference", type="string", length=255)
     */
    private $shopreference;
    
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="items")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;
    
     /**
     * @ORM\ManyToOne(targetEntity="Shop", inversedBy="items")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop;    
    
     /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=32)
     */
    private $state;
    
    /**
     * @var integer $stock
     *
     * @ORM\Column(name="stock", type="integer")
     */
    private $stock;
    
    /**
     * @var integer $pvp
     *
     * @ORM\Column(name="pvp", type="float")
     */

    private $pvp;

    
    /**
     * @var integer $price
     *
     * @ORM\Column(name="price", type="integer")
     */

    private $price;    

    
    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="item")
     */
    protected $orders;

    /**
     * @var array $feature
     *
     * @ORM\ManyToMany(targetEntity="Feature", inversedBy="item")
     * @ORM\JoinTable(
     *    name="item_feature",
     *    joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="feature_id", referencedColumnName="id")}
     * )
     */
    private $feature;

    /**
     * @var array $attr
     *
     * @ORM\ManyToMany(targetEntity="Attr", inversedBy="item")
     * @ORM\JoinTable(
     *    name="item_attr",
     *    joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="attr_id", referencedColumnName="id")}
     * )
     */
    private $attr;
    
        
     /**
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;
        
    /**
     * @var date $date_add
     *
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $date_add;
    
    /**
     * @var date $date_upd
     *
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private $date_upd;
    
    /**
     * @var date $date_sold
     *
     * @ORM\Column(name="date_sold", type="datetime", nullable=true)
     */
    private $date_sold;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
    	$this->setDateAdd(new \DateTime());
    	$this->setDateUpd(new \DateTime('now'));
    	$this->state = "STATE_RAW";
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
     * Set PS_id
     *
     * @param string $PS_id
     */
    public function setPSId($PS_id)
    {
    	$this->PS_id = $PS_id;
    }
    /**
     * Get PS_id
     *
     * @return integer
     */
    public function getPSId()
    {
    	return $this->PS_id;
    }
    
    /**
     * Set reference
     *
     * @param string $reference
     */
    public function setReference($reference)
    {
    	$this->reference = $reference;
    }
    
    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
    	return $this->reference;
    }

    /**
     * Set shopreference
     *
     * @param string $shopreference
     */
    public function setShopreference($shopreference)
    {
    	$this->shopreference = $shopreference;
    }
    
    /**
     * Get shoprreference
     *
     * @return string
     */
    public function getShopreference()
    {
    	return $this->shopreference;
    }

    
    /**
     * Set product
     *
     * @param Estokin\PanelBundle\Product $product
     */
    public function setProduct ($product)
    {
    	$this->product = $product;
    }
    
    /**
     * Get product
     *
     * @return Estokin\PanelBundle\Product
     */
    public function getProduct()
    {
    	return $this->product;
    }
    public function getProductState(){
    	return $this->product->getState();    
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
     * Set stock
     *
     * @param integer $stock
     */
    public function setStock($stock)
    {
    	$this->stock = $stock;
    }
    
    /**
     * Get stock
     *
     * @return integer
     */
    public function getStock()
    {
    	return $this->stock;
    }
     
     /**
     * Set pvp
     *
     * @param float $pvp
     */
    public function setPvp($pvp)
    {
    	$this->pvp = $pvp;
    }
    
    /**
     * Get pvp
     *
     * @return float
     */
    public function getPvp()
    {
    	return $this->pvp;
    }
    
     /**
     * Set price
     *
     * @param float $price
     */
    public function setPrice($price)
    {
    	$this->price = $price;
    }
    
    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
    	return $this->price;
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
     * Has orders
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function hasOrders()
    {
    	if(count($this->orders) > 0) return true;
    	else return false;
    }
    
    
    /**
     * Set feature
     *
     * @param Estokin\PanelBundle\Feature $feature
     */
    public function setFeature($feature)
    {
        $this->feature = $feature;
    }

    /**
     * Get feature
     *
     * @return Estokin\PanelBundle\Feature 
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Add attr
     *
     * @param Estokin\PanelBundle\Attr $attr
     */
    public function addAttr($attr)
    {
        $this->attr[] = $attr;
    }
    
     /**
     * Delete attrs
     *
     */
    public function delAttrs()
    {
        $this->attr = null;
    }

    /**
     * Get attr
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAttr()
    {
        return $this->attr;
    }
    
    public function hasAttr(){ 
	    if ($this->getAttr()->count() > 0) return true;
	    else return false;
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
     * @param date $dateAdd
     */
    public function setDateAdd($dateAdd)
    {
    	$this->date_add = $dateAdd;
    }
    
    /**
     * Get date_add
     *
     * @return date
     */
    public function getDateAdd()
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
    	$prod = $this->getProduct();
    	return $prod->getBrand()." - ".$prod->getName();
    }
    
    public function getCompleteName(){
	    $name = $this->getName();
	    $i = 0;
	    $end = count($this->attr)-1;
	    if( $this->attr && $this->attr->count() > 0 ) {
		    $name = $name." (";
	    	foreach( $this->attr as $attr ){
		    	 $name = $name.$attr->getAttrSet()->getName()." ".$attr->getValue();
		    	 if($i == $end) $name = $name.")";
		    	 else $name = $name.", ";
		    	 $i++;
	    	}
    	}
    	return $name;
    }
    
    public function uploadItem($id){

    	$this->setPSId($id);
    	$this->date_upd = new \DateTime();
    	$this->setState('STATE_ONLINE');
    	return true;
    }
    
    /**
     * Sell Item
     *
     * @return boolean
     */
    public function sellItem($quantity){
    	//if there is no stock, return false
    	if($this->stock < $quantity) return false;
    	//else, subtract 1 from stock
    	$this->stock = $this->stock - $quantity;
    	//if now stock is empty, change state to "valid" in order not to see in "online table"
    	if($this->stock < 1){
    		//$this->state = "STATE_VALID";
    	}
    	return true;    
    }
    
    public function __toString(){
    	return $this->reference;
    }

}