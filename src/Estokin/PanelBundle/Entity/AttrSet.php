<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\AttrSet
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class AttrSet
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
     * @var integer $PS_id
     *
     * @ORM\Column(name="PS_id", type="integer")
     */
    private $PS_id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Attr", mappedBy="attrSet")
     */
    private $attr;
    

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
     * @param int $PS_id
     */
    public function setPSid($PS_id)
    {
    	$this->PS_id = $PS_id;
    }
    
    /**
     * Get PS_id
     *
     * @return integer
     */
    public function getPSid()
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
     * Add attr
     *
     * @param Estokin\PanelBundle\Product $attr
     */
    public function addAttr($attr)
    {
    	$this->prodcuts[] = $attr;
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
    
    /**
     *
     * @return string
     */
    public function __toString()
    {
    	return $this->name;
    }
}