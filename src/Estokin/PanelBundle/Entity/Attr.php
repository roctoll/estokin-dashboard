<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\Attr
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\AttrRepository")
 */
class Attr
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
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;
    
    /**
     * @ORM\ManyToOne(targetEntity="AttrSet", inversedBy="attr")
     * @ORM\JoinColumn(name="attrSet_id", referencedColumnName="id")
     */
    protected $attrSet;

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
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }
        
    /**
     * Set attrSet
     *
     * @param Estokin\PanelBundle\AttrSet $attrSet
     */
    public function setAttrSet ($attrSet)
    {
    	$this->attrSet = $attrSet;
    }
    
    /**
     * Get attrSet
     *
     * @return Estokin\PanelBundle\AttrSet
     */
    public function getAttrSet()
    {
    	return $this->attrSet;
    }
    
    public function __toString(){
    	return $this->value;
    }
}