<?php

namespace Estokin\PanelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estokin\PanelBundle\Entity\Message
 *
 * @ORM\Entity(repositoryClass="Estokin\PanelBundle\Repository\MessageRepository")
 */
class Message
{
	
	/**
	 * Message types.
	 */
	const
		TICKET = 0,
		NOTIFICATION = 1,
		DEAD = 2;
		
	 /**
     * Message priority.
     */
    const
        ALTA	= 0,
        MEDIA	= 1,
        BAJA	= 2;
	
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=32)
     */
    private $type;
    
    /**
     * @var text $subject
     *
     * @ORM\Column(name="subject", type="text", nullable=true)
     */
    private $subject;

    /**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var integer $priority
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var date $date_add
     *
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $date_add;

    /**
     * @ORM\ManyToOne(targetEntity="Shop", inversedBy="messages")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    private $shop;
    
    /**
     * @var boolean $admin
     * 0 -> Autor Tienda, destinatario Admin
     * 1 -> Autor Admin, destinatario Tienda
     *
     * @ORM\Column(name="admin", type="boolean")
     */    
    private $admin;
    
     /**
     * @var boolean $main
     * 0 -> It's an other message answer
     * 1 -> It's a main message
     *
     * @ORM\Column(name="main", type="boolean")
     */    
    private $main;
    
    /**
     * @var Message $replies
     * 
     * @ORM\OneToMany(targetEntity="Message", mappedBy="parent")
     */
    private $replies;
    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="replies")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;


    /**
     * @var integer $is_read
     *
     * 0 = is read
     * 1 = is new for user
     * 2 = is new for admin
     *
     * @ORM\Column(name="is_read", type="integer")
     */
    private $is_read;


    
    /**
     * Constructor alt.
     */
    public function __construct($type, $admin = true, $main = true)
    {
    	$this->type = $type;
    	$this->admin = $admin;
    	$this->main = $main;    	
    	$this->is_read = 1;
    	$this->setDateAdd(new \DateTime('now'));
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
     * Set subject
     *
     * @param text $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return text 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
    	$this->type = $type;
    }
    
    /**
     * Get type
     *
     * @return text
     */
    public function getType()
    {
    	return $this->type;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
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
      * Set parent
      *
      * @param Estokin\PanelBundle\Message $parent
      */
     public function setParent($parent)
     {
         $this->parent = $parent;
     }

     /**
      * Get parent
      *
      * @return Estokin\PanelBundle\Message 
      */
     public function getParent()
     {
         return $this->parent;
     }

    /**
     * Set is_read
     *
     * @param integer $isRead
     */
    public function setIsRead($isRead)
    {
        $this->is_read = $isRead;
    }

    /**
     * Get is_read
     *
     * @return integer 
     */
    public function getIsRead()
    {
        return $this->is_read;
    }
    /**
     * Set admin
     *
     * @param boolean $admin
     */
    public function setAdmin($admin)
    {
    	$this->admin = $admin;
    }
    
    /**
     * Get admin
     *
     * @return boolean
     */
    public function getAdmin()
    {
    	return $this->admin;
    }
    
     /**
     * Set main
     *
     * @param boolean $main
     */
    public function setMain($main)
    {
    	$this->main = $main;
    }
    
    /**
     * Get main
     *
     * @return boolean
     */
    public function getMain()
    {
    	return $this->main;
    }
     
     /**
     * Add reply
     *
     * @param Estokin\PanelBundle\Message $replies
     */
    public function addReplies($replies)
    {
    	$this->replies[] = $replies;
    }
    
    /**
     * Get replies
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getReplies()
    {
    	return $this->replies;
    }
}