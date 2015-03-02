<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Estokin\PanelBundle\Entity\Product;


/**
 * Estokin\PanelBundle\Entity\Product
 *
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\CategoryRepository")
 */

class Category
{
	 /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;    

	 /**
     * @var integer $level
     *
     * @ORM\Column(name="level", type="integer")
     */
    private $level;
    
    /**
     * @var integer $PS_id
     *
     * @ORM\Column(name="PS_id", type="integer", nullable=true)
     */
    private $PS_id;
    
    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     */
    protected $products;
    
    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    protected $categories;
    
    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="categories")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
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
     * Set level
     *
     * @param integer $level
     */
    public function setLevel($level)
    {
    	$this->level = $level;
    }
    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
    	return $this->level;
    }
        
    /**
     * Set PS_id
     *
     * @param integer $PS_id
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
     * Add products
     *
     * @param Estokin\PanelBundle\Product $products
     */
    public function addProducts($products)
    {
        $this->prodcuts[] = $products;
    }

    /**
     * Get products
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }
    
    /**
     * Add categories
     *
     * @param Estokin\PanelBundle\Category $categories
     */
    public function addCategories($categories)
    {
        $this->categories[] = $categories;
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }
    
     /**
     * Set parent
     *
     * @param Estokin\PanelBundle\Category $parent
     */
    public function setParent ($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Estokin\PanelBundle\Category 
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * to String
     * 
     * @return string
     */
    public function __toString()
    {
    	return $this->name;
    }
    
    
}