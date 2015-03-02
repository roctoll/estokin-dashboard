<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\Feature
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Feature
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
     * @var string $field
     *
     * @ORM\Column(name="field", type="string", length=255)
     */
    private $field;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
	 * @ORM\ManyToOne(targetEntity="Product", inversedBy="features")
	 * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
	 */
    private $product;


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
     * Set field
     *
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * Get field
     *
     * @return string 
     */
    public function getField()
    {
        return $this->field;
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
     * Set product
     *
     * @param object $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return object 
     */
    public function getProduct()
    {
        return $this->product;
    }
}