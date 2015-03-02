<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\Brand
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Brand
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
     * @var array $products
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="brand")
     */
    private $products;


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
     * @param Estokin\PanelBundle\Product $product
     */
    public function addProducts($product)
    {
    	$this->prodcuts[] = $product;
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
    
    public function __toString(){
    	return $this->name;
    }
}