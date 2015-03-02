<?php

namespace Estokin\PanelBundle\Entity;

use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Estokin\PanelBundle\Entity\Category;
use Estokin\PanelBundle\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Estokin\PanelBundle\Entity\Product
 *
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\ProductRepository")
 */
class Product
{
	 /**
     * Product states.
     */
    const
        STATE_RAW		= 0,
        STATE_VALID		= 1,
        STATE_ONLINE	= 2,
        STATE_BLOCK		= 3;
        
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $reference
     *
     * @ORM\Column(name="code", type="string", length=255, nullable="true")
     */
    private $code;

    
    /**
     * @var integer $season
     *
     * @ORM\Column(name="season", type="integer")
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="Shop", inversedBy="products")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop_add;
    
    /**
     * @ORM\ManyToOne(targetEntity="Brand", inversedBy="products")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id")
     */
    protected $brand;
    
    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="product")
     */
    protected $items;

    /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=32)
     * @Assert\NotBlank()
     */
    private $state;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var array $tags
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="products")
     * @ORM\JoinTable(
     *    name="product_tags", 
     *    joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    private $tags;
    

    /**
     * @var string $image
     +
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */        
    protected $image;
    
    
    /**
     * @var text $comment
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;


    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable="true")
     */
    private $price;

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
     * Constructor.
     */
    public function __construct()
    {
        //$this->tags = new ArrayCollection();
        //@TODO Available is not working
        $this->setState('STATE_RAW');
        $this->setDateAdd(new \DateTime());
        $this->setDateUpd(new \DateTime('now'));
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get bigname
     *
     * @return string
     */
    public function getBigName()
    {
    	return $this->brand." - ".$this->name." (".$this->season.")";
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set season
     *
     * @param string $season
     */
    public function setSeason($season)
    {
    	$this->season = $season;
    }
    /**
     * Get season
     *
     * @return integer
     */
    public function getSeason()
    {
    	return $this->season;
    }
    
    /**
     * Add items
     *
     * @param Estokin\PanelBundle\Item $items
     */
    public function addItems($items)
    {
    	$this->prodcuts[] = $items;
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
     * Get items num
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getNumItems()
    {
    	$c = 0;
    	foreach($this->items as $item){
			if($item->getState() != 'STATE_DEATH') $c++;    	
    	}
    	return $c;
    }
    
    public function hasItems(){ 
	    if($this->items->count() > 0) return true;
	    else return false;
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
     * Set excerpt
     *
     * @param text $excerpt
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
    }

    /**
     * Get excerpt
     *
     * @return text 
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
        /**
     * Set description
     *
     * @param text $description
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
     * Set tags
     *
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get tags
     *
     * @return array 
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    /**
     * Set brand
     *
     * @param Estokin\PanelBundle\Brand $brand
     */
    public function setBrand ($brand)
    {
    	$this->brand = $brand;
    }
    
    /**
     * Get brand
     *
     * @return Estokin\PanelBundle\Brand
     */
    public function getBrand()
    {
    	return $this->brand;
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
     * Set category
     *
     * @param Estokin\PanelBundle\Category $category
     */
    public function setCategory ($category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Estokin\PanelBundle\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }
    
     /**
      * Set shop who have added the product
      *
      * @param Estokin\PanelBundle\Shop $shop
      */      
     public function setShop_add ($shop)
     {
         $this->shop = $shop_add;
     }

     /**
      * Get shop who have added the product
      *
      * @return Estokin\PanelBundle\Shop 
      */
     public function getShop_add()
     {
         return $this->shop_add;
     }
     
    /**
     * Set image
     *
     */
    public function setImage($path)
    {
    	$this->image = $path;
    }
    
    /**
     * Get image
     *
     */
    public function getImage()
    {
    	if($this->image != null) return 'http://'.$_SERVER['HTTP_HOST']."/app/uploads/images/".$this->image;
    	else return 'http://'.$_SERVER['HTTP_HOST']."/app/uploads/images/thumbnail.png"; 
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
     * 
     * Methods to handle the product state
     *
     * @param bool $valid     
     */
    
    public function validateProduct($valid){ 

    	if($valid && ($this->state == "STATE_RAW" || $this->state == "STATE_VALID")){
	    	$this->state = "STATE_VALID"; 
	    	return true;   	    		
    	}
    	else if(!$valid && ($this->state == "STATE_RAW" || $this->state == "STATE_VALID")){
	    	$this->state = "STATE_RAW"; 
	    	return true;   	    		
    	}
    	return false;
    }
    public function editProduct(){
    	if($this->state == "STATE_VALID" || $this->state == "STATE_RAW"){
    		$this->state = "STATE_RAW";
    		$this->date_upd = new \DateTime(); 
    		return true; 
    	}
    	return false;
    }
    public function uploadProduct($psid){
    		$this->PS_id = $psid;
    		$this->state = "STATE_ONLINE";
	    	$this->date_upd = new \DateTime();
			return true;
    }
	public function blockProduct(){
    	if($this->state == "STATE_ONLINE" || $this->state == "STATE_UNAV"){
	    	$this->state = "STATE_BLOCK"; 
	    	$this->date_upd = new \DateTime();
	    	return true;   	    		
    	}
    	return false;
    }
    public function unblockProduct(){
    	if($this->state == "STATE_BLOCK" || $this->state == "STATE_UNAV"){
    		$this->state = "STATE_ONLINE";
    		$this->date_upd = new \DateTime();
    		return true;
    	}
    	return false;
    }

    public function __toString(){
    	return $this->name;
    }
    
    public function toArray() {
    	return Array(
    			'id' => $this->id,
    			'name' => $this->name,
    			'reference' => $this->reference,
    			'category' => $this->category->getName(),
    			'state' => $this->state,
    			'brand' => $this->brand,
    			'description' => $this->description,
    			//'tags'
    			'shop_add' => $this->shop->getName(),
    			'date_add' => $this->date_add,
    			'date_upd' => $this->date_upd,
				'date_sold'=> $this->date_sold,    	
    			);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}