<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Estokin\PanelBundle\Entity\Product;


/**
 * Estokin\PanelBundle\Entity\Tag
 *
 * @ORM\Entity
 */
class Tag
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
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="tags")
     */
    protected $products;

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
     * to String
     * 
     * @return string
     */
    public function __toString()
    {
    	return $this->name;
    }
}