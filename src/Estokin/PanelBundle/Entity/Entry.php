<?php

namespace Estokin\PanelBundle\Entity;

use Symfony\Component\Validator\Constraints\Date;

use Doctrine\ORM\Mapping as ORM;


/**
 * Estokin\PanelBundle\Entity\Entry
 *
 * @ORM\Entity
 */
class Entry
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
	 * @var string $concept
	 * 
	 * @ORM\Column(name="concept", type="string")
	 */
	private $concept;

	/**
	 * @var integer $value
	 * 
	 * @ORM\Column(name="value", type="integer")
	 */
	private $value;
	
	/**
	 * Constructor.
	 */
	public function __construct($concept, $value)
	{
		$this->concept = $concept;
		$this->value = $value;
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
     * Set concept
     *
     * @param string $concept
     */
    public function setConcept($concept)
    {
        $this->concept = $concept;
    }

    /**
     * Get concept
     *
     * @return string 
     */
    public function getConcept()
    {
        return $this->concept;
    }

    /**
     * Set value
     *
     * @param integer $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }
}