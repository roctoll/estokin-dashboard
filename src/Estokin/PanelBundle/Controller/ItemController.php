<?php

namespace Estokin\PanelBundle\Controller;

use Estokin\PanelBundle\EstokinPanelBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\Item;
use Estokin\PanelBundle\Entity\Message;
use Estokin\PanelBundle\Entity\Order;
use Estokin\PanelBundle\Entity\Product;
use Estokin\PanelBundle\Entity\AttrSet;
use Estokin\PanelBundle\Entity\Attr;
use Estokin\PanelBundle\Entity\Document;
use Estokin\PanelBundle\Entity\Category;
use Estokin\PanelBundle\Form\ItemType;
use Estokin\PanelBundle\Form\ProductType;


/**
 * Item controller.
 *
 * @Route("")
 */
class ItemController extends Controller
{
    /**
     * Lists all Item entities.
     *
     * @Route("/", name="item")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	return $this->redirect($this->generateUrl('admin_index'));
        }
        
        //Total revenue
        $transactions = $em->getRepository('EstokinPanelBundle:Transaction')->findByShop($user->getId());
        $cash_total = 0;
        foreach($transactions as $trans){
	        $cash_total = $cash_total + $trans->getAmount();
        }
					
        //Pendent cash
        $cash_pendent = $em->getRepository('EstokinPanelBundle:Transaction')->findOneBy(
					array('shop' => $user->getId(), 'state' => 'STATE_PENDENT'),
					array('date_add' => 'DESC'));
        if($cash_pendent) $cash_pendent = $cash_pendent->getAmount();
        else $cash_pendent = 0;
        
        //Total orders
        $orders_total = $em->getRepository('EstokinPanelBundle:Order')->getNumTotal($user->getId());
        
        //Pendent orders
        $orders_pendent = $em->getRepository('EstokinPanelBundle:Order')->getNumSold($user->getId());
        
        //Total items
        $items_total = $em->getRepository('EstokinPanelBundle:Item')->getNumTotal($user->getId());             
                
        return array('cash_total' => $cash_total, 'cash_pendent' => $cash_pendent, 'orders_total' => $orders_total, 'orders_pendent' => $orders_pendent, 'items_total' => $items_total, 'user' => $user);
    }
    
    /**
     * (Admin Home) Lists all Item entities.
     *
     * @Route("/admin", name="admin_index")
     * @Template()
     */
    public function indexAdminAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
    		return $this->redirect($this->generateUrl('item'));
    	}

    	//Total revenue
        $cash_total = $em->getRepository('EstokinPanelBundle:Transaction')->getTotalCashA();
					
        //Pendent cash
        $cash_pendent = $em->getRepository('EstokinPanelBundle:Transaction')->findOneBy(
					array('shop' => $user->getId(), 'state' => 'STATE_PENDENT'),
					array('date_add' => 'DESC'));
        if($cash_pendent) $cash_pendent = $cash_pendent->getAmount();
        else $cash_pendent = 0;
        
        //Total orders
        $orders_total = $em->getRepository('EstokinPanelBundle:Order')->getNumTotal($user->getId());
        
        //Pendent orders
        $orders_pendent = $em->getRepository('EstokinPanelBundle:Order')->getNumSold($user->getId());
        
        //Total items
        $items_total = $em->getRepository('EstokinPanelBundle:Item')->getNumTotal($user->getId());
    	
    	return $this->render('EstokinPanelBundle:Item:index-a.html.twig', array('cash_total' => $cash_total, 'cash_pendent' => $cash_pendent, 'orders_total' => $orders_total, 'orders_pendent' => $orders_pendent, 'items_total' => $items_total, 'user' => $user));
    }

    /**
     * Get the info to fill the base layout data
     *
     * @Route("/layoutdata", name="layout_data")
     */
    public function layoutDataAction(){
    	$request = $this->getRequest();
    	if ($request->isXmlHttpRequest()){
    		
    		try{
    			
	    		$em = $this->getDoctrine()->getEntityManager();
	    		$user = $this->get("security.context")->getToken()->getUser();
	    		if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
		    		$orders = $em->getRepository('EstokinPanelBundle:Order')->getNumSoldA();
		    		$items = $em->getRepository('EstokinPanelBundle:Item')->getNumTotalA();             	    		
		    		$msg = $em->getRepository('EstokinPanelBundle:Message')->getNumNewTicketsA(); 
	
	    			return $this->returnJSON(array(
	    					'success' => true,
	    					'notifications' => 0,
	    					'items' => $items,
	    					'orders' => $orders,
	    					'msg' => $msg,
	    			), 200);		    		
	    		}
	    			
	    		$orders = $em->getRepository('EstokinPanelBundle:Order')->getNumSold($user->getId());
	    		$items = $em->getRepository('EstokinPanelBundle:Item')->getNumTotal($user->getId());             	    		
	    		$not = $em->getRepository('EstokinPanelBundle:Message')->getNumNewNotifications($user->getId());
	    		$msg = $em->getRepository('EstokinPanelBundle:Message')->getNumNewTickets($user->getId()); 

    			return $this->returnJSON(array(
    					'success' => true,
    					'notifications' => $not,
    					'items' => $items,
    					'orders' => $orders,
    					'msg' => $msg,
    			), 200);
    			
    		}catch (Exception $e){
    			return $this->returnJSON(array(
    					'success' => false,
    					
    			), 500);
    		}
    	}
    	return false;	
    }
    /**
     * Finds and displays a Item entity.
     *
     * @Route("/item/{id}/show", name="item_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
        
        //restricted access to other user's items
        $user = $this->get("security.context")->getToken()->getUser();
        
        if($user->isShop() && $entity->getShop() != $user){
        	throw $this->createNotFoundException('No acces to this item.'); 
        }

        $pslink = $this->container->getParameter('PS_PROD_LINK');
        $pslink = substr_replace($pslink, $entity->getProduct()->getPSId(), 75, 2);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
        	'pslink'	=> $pslink,
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Item entity.
     *
     * @Route("/item/new", name="item_new")
     * @Template()
     */
    public function newAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	
    	$attrSets = $em->getRepository('EstokinPanelBundle:AttrSet')->findAll();    	
        $plist = $em->getRepository('EstokinPanelBundle:Product')->getProductNames();
        $slist = $em->getRepository('EstokinPanelBundle:Shop')->findAll();
        $clist = $em->getRepository('EstokinPanelBundle:Category')->getCategoriesTree();
        
        $ctree = $this->doTree($clist); 

        $product = new Product();
        $formP   = $this->createForm(new ProductType(), $product);        
        $user = $this->get("security.context")->getToken()->getUser();
        
/*
        $document = new Document();
	    $docForm = $this->createFormBuilder($document)
	        ->add('name')
	        ->add('file')
	        ->getForm();	
*/
	        
        return array(
        	'attrSets' => $attrSets,	
            'plist' => $plist,
        	'slist' => $slist,
        	'ctree' => $ctree,
        	'user'	 => $user,
        	'formP'	 => $formP->createview(),
/*        		'docForm' => $docForm->createView(),    	 */
        );
    }

    /**
     * Creates a new Item entity.
     *
     * @Route("/item/create", name="item_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:Item:new.html.twig")
     */
    public function createAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();    	 
        $request = $this->getRequest(); 

        $name = $request->get('estokin_panelbundle_producttype');  
        $name = $name['name']; 
    	$product = $em->getRepository('EstokinPanelBundle:Product')->findOneByName($name);
    	$attrSets = $em->getRepository('EstokinPanelBundle:AttrSet')->findAll();
    	
    	//1. If product doesn't exist, it is created. 
    	//2. The item is created.
    	//3. If the product is online, item is uploaded. [Mode manual]
    	
    	//case it's a new product
    	if(!$product){
    		
	    	$product = new Product();
	    	$errors = array();
	    	$user = $this->get("security.context")->getToken()->getUser();
	    	$form = $this->createForm(new ProductType(), $product); 
	        $form->bindRequest($request);

	        if ($form->isValid()) {

				if($_FILES['estokin_panelbundle_producttype']['name']['image'] != ""){	        	
		        	$file = $form['image']->getData();
		        	$dir = __DIR__ . '/../../../../web/uploads/images';
		        	$extension = $file->guessExtension();
					if (!$extension) {
					    // extension cannot be guessed
					    $extension = 'bin';
					}
					$file->move($dir, 'image-'.preg_replace('[\s+]', '_', $product->getName()).'.'.$extension);				
					$product->setImage('image-'.preg_replace('[\s+]', '_', $product->getName()).'.'.$extension);
				}
				
		    	$newbrand = $request->get('newbrand');
		    	if($newbrand) $product->setComment($newbrand);
				
	            $em->persist($product);

	            $arr = $this->get('session')->getFlashes();
	            $arr['good'][] = "El producto: ".$product->getBigName()." ha sido creado con éxito.";
	            $this->get('session')->setFlashes($arr);
	             
	        }
	        else{
	        	$arr = $this->get('session')->getFlashes();
	            $arr['bad'][] = "El producto: ".$product->getBigName()." no ha sido creado con éxito.";
	        	
	        	foreach($form->getErrors() as $error)
		        {	
		             $errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
		             $arr['bad'][] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
		        }
	            $this->get('session')->setFlashes($arr);

		        $attrSets = $em->getRepository('EstokinPanelBundle:AttrSet')->findAll();
		        $plist = $em->getRepository('EstokinPanelBundle:Product')->getProductNames();
		        	
		        return array(
	        		'attrSets' => $attrSets,
	        		'plist' => $plist,
		        	'errors' => $errors,
		        	'user' 	 => $user,
		            'formP'   => $form->createView()
		        );
	        }
    	} 
    	   
        $entity  = new Item(); 
    	$entity->setProduct($product);
    	$shop = $this->get("security.context")->getToken()->getUser(); 
    	
    	//is the user is a shop, the item is associated to him, if not (user=admin) the shop is retrieved from the form
    	if($shop->isShop()) $entity->setShop($shop);
    	else{ 
    		$shop = $em->getRepository('EstokinPanelBundle:Shop')->findOneByName($request->get('shop'));
    		$entity->setShop($shop);
    	}
    	
    	$gog_img = '';
    	
    	//Should check the params!
    	$stock = $request->get('stock');
    	$pvp = $request->get('pvp');
    	$price = $request->get('price'); 
    	$talla = $request->get('size'); 
    	$color = $request->get('color'); 
    	$shopreference = $request->get('shopreference'); 
    	$gog_img = $request->get('google_image');
       	$entity->setStock($stock);
    	$entity->setPvp($pvp);
    	$entity->setPrice($price);
    	$entity->setShopreference($shopreference);
    	$entity->setComment('Talla: '.$talla."\n Color: ".$color);
    	if ($gog_img != '') $entity->setComment($entity->getComment()."\n Google image: ".$gog_img);
    	
    	$ref = str_pad($shop->getId(), 3, "0", STR_PAD_LEFT).'-';
    	$ref = $ref.str_pad($entity->getProduct()->getCategory()->getId(), 5, "0", STR_PAD_LEFT);
    	$ref = $ref.str_pad($entity->getId(), 4, "0", STR_PAD_LEFT);

    	$entity->setReference($ref);
    		
    	//set all the attributes of the item
    	foreach($attrSets as $aset){ 
    		$attr = $request->get($aset->getName());
    		if($attr){
    			$entity->addAttr($em->getRepository('EstokinPanelBundle:Attr')->findOneByValue($attr));
    		}
    	}    	
    	 
    	//If the product is online, the item is uploaded too
/*		[Mode manual]
    	if($product->getPSId()){

	    	$uploader = $this->get('uploader');    		
    		if($psid = $uploader->PS_upload_item($entity)){
	    		$entity->uploadItem($psid);	    				    		
	    		$em->persist($entity);
	    		$em->flush();
	    		
	    		$arr = $this->get('session')->getFlashes();
	            $arr['good'][] = "El item: ".$entity->getCompleteName()." ha sido creado y publicado con éxito.";
	            $this->get('session')->setFlashes($arr);
	    		
	    		return $this->redirect($this->generateUrl('item_stock'));
	    	}
	    	else{
		    	$arr = $this->get('session')->getFlashes();
			    $arr['bad'][] = "Se ha producido un error al publicar el item '".$entity->getCompleteName()."', ya ha sido reportado al administrador.";
			    $this->get('session')->setFlashes($arr);
			    $this->notifier($entity->getShop(), 'upload fail in item: '.$entity->getId(), true, $em);
	    	}
    	}
*/
    	try{
		    $em->persist($entity);	    
		    $em->flush();
		}
		catch(Exception $e){
			$arr = $this->get('session')->getFlashes();
            $arr['bad'][] = "El item: ".$entity->getCompleteName()." no ha sido creado con éxito.";
            $this->get('session')->setFlashes($arr);
            $this->notifier($entity->getShop(), 'Error al crear el item: '.$entity->getId(), true, $em);
            return $this->redirect($this->generateUrl('item_new'));
		}
	    $arr = $this->get('session')->getFlashes();
	    $arr['good'][] = "El item: ".$entity->getCompleteName()." ha sido creado con éxito.";
	    $this->get('session')->setFlashes($arr);
	    
	    return $this->redirect($this->generateUrl('item_list'));

    }

    /**
     * Displays a form to edit an existing Item entity.
     *
     * @Route("/item/{id}/edit", name="item_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $editForm = $this->createForm(new ItemType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Item entity.
     *
     * @Route("/item/{id}/update", name="item_update")
     * @Method("post")
     * @Template("EstokinPanelBundle:Item:edit.html.twig")
     */
    public function updateAction($id)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }
        
        $editForm   = $this->createForm(new ItemType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('item_list'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
     /**
     * Fast Edit an existing Item entity.
     *
     * @Route("/item/{id}/fastupdate", name="item_fastupdate")
     * @Method("post")
     */
    public function fastupdateAction($id)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
    	$attrSets = $em->getRepository('EstokinPanelBundle:AttrSet')->findAll();
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }   

        $request = $this->getRequest(); 

        $entity->setShopreference($request->get('shopreference'));
        $entity->setPrice($request->get('price'));

        $entity->setComment($entity->getComment()." | ".$request->get('comment'));

/*
        $entity->delAttrs();
 
        //set all the attributes of the item
    	foreach($attrSets as $aset){ 
    		$attr = $request->get($aset->getName());
    		if($attr){ 
    			$entity->addAttr($em->getRepository('EstokinPanelBundle:Attr')->findOneByValue($attr));
    		}
    	} 
*/
                
        try{
            $em->persist($entity);
            $em->flush();
            
            $arr = $this->get('session')->getFlashes();
            $arr['good'][] = "El item: ".$entity->getCompleteName()." ha sido actualizado con éxito.";
            $this->get('session')->setFlashes($arr);

            return $this->redirect($this->generateUrl('item_list'));
        }
        catch(Expection $e){
        	$arr = $this->get('session')->getFlashes();
            $arr['bad'][] = "El item: ".$entity->getCompleteName()." no se ha actualizado correctamente.";
            $this->get('session')->setFlashes($arr);
            return $this->redirect($this->generateUrl('item_list'));

        }
    }

    /**
     * Deletes a Item entity.
     *
     * @Route("/item/{id}/delete", name="item_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
    
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();
        
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
        
        if (!$entity) {
        	throw $this->createNotFoundException('Unable to find Item entity.');
        }
        if($user->isShop() && $entity->getShop()->getId() != $user->getId()){
        	//@TODO exception correcte
        	throw $this->createNotFoundException('No acces to this item');
        }
                                
        
        if ($request->isXmlHttpRequest()){
        
        	try{
        	
        		//If the item is online, first remove it from PRESTASHOP        		    		
        		if($entity->getState() == "STATE_ONLINE"){
/*
		       		$uploader = $this->get('uploader');
		       		$uploader->PS_remove_item($entity->getPSId());	
*/
					$message = "La tienda ".$entity->getShop()->getName()." ha retirado el producto ".$entity->getName();
					mail('roctoll@gmail.com', 'Actualización stock', $message);

					// If Item has not orders, it's completely removed
					if(!$entity->hasOrders()) $em->remove($entity); 
					
					// If Item has orders change to STATE_DEAD
					else { 
						$entity->setState('STATE_DEAD'); 					
						$em->persist($entity);
					}
					
					$em->flush();
					return $this->returnJSON(array('success' => true ));	       			
        		} 
        		
        		else if($entity->getState() == "STATE_RAW"){
	        		$em->remove($entity);
	        		$em->flush();

	        	}
        		return $this->returnJSON(array(
        				'success' => true,
        				'id' => $id,
        				'product' => $entity->getName(),
        		), 200);
        	}catch (Exception $e){
        		return $this->returnJSON(array(
        				'success' => false,
        				'id' => $id,
        				'product' => $entity->getName(),
        				'exception => $e',
        		), 500);
        	}
        }

        $form->bindRequest($request);

        if ($form->isValid()) {
        	
        	//If the item is online, first remove it from PRESTASHOP
        	if($entity->getPSId()) {
        	$uploader = $this->get('uploader');    		
        		if($uploader->PS_remove_item($entity->getPSId())){
	        		$em->remove($entity);
	        		$em->flush();
	        		$arr = $this->get('session')->getFlashes();
		            $arr['good'][] = "El item ".$entity->getProduct()->getBigName(). " ha sido eliminado.";
		            $this->get('session')->setFlashes($arr);
	        	}
	        	else{
		        	$em->remove($entity);
	        		$em->flush();
	        		$arr = $this->get('session')->getFlashes();
	        		$arr['bad'][] = "El item ".$entity->getProduct()->getBigName(). " no existe en PS y ha sido eliminado del sistema.";
	        		$this->get('session')->setFlashes($arr);
	        	}
        	}
        	else{
	        	$em->remove($entity);
	        	$em->flush();
	        	$arr = $this->get('session')->getFlashes();
	            $arr['good'][] = "El item ".$entity->getProduct()->getBigName(). " ha sido eliminado.";
	            $this->get('session')->setFlashes($arr);
        	}        	             
	        return $this->redirect($this->generateUrl('item_list'));
        }
        else{
        
        	foreach($form->getErrors() as $error)
        	{
        		$errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        	}
        	$arr = $this->get('session')->getFlashes();
            $arr['bad'][] = "El item ".$entity->getProduct()->getBigName(). " no ha podido ser eliminado.";
            $this->get('session')->setFlashes($arr);
        	
        	$deleteForm = $this->createDeleteForm($id);
        	
        	return $this->redirect($this->generateUrl('item_show', array('id' => $id)), array(
        			'entity'      => $entity,
        			'delete_form' => $deleteForm->createView(),
        	));
        }
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    } 
    
        
    /**
     * Lists all Items entities.
     *
     * @Route("/item/list", name="item_list")
     * @Template()
     */
    public function listItemsAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();
    	$attrset = $em->getRepository('EstokinPanelBundle:AttrSet')->FindAll();
    	if(!$user->isShop()) {
    		$entities = $em->getRepository('EstokinPanelBundle:Item')->getForListA();
    		return $this->render('EstokinPanelBundle:Item:list.html.twig', array('entities' => $entities, 'attrset' => $attrset));
    	}
    	$entities = $em->getRepository('EstokinPanelBundle:Item')->getforList($user->getId());

    	return $this->render('EstokinPanelBundle:Item:list.html.twig', array('entities' => $entities, 'user' => $user, 'attrset' => $attrset));
    }
    
            
    /**
     * Lists all Items and Products to upload.
     *
     * @Route("/item/todolist-a", name="item_todolist")
     * @Template()
     */
    public function todolistItemsAction()
    {
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
	    
	    $em = $this->getDoctrine()->getEntityManager();
    	$attrset = $em->getRepository('EstokinPanelBundle:AttrSet')->FindAll();

    	$items = $em->getRepository('EstokinPanelBundle:Item')->findByState('STATE_RAW');
    	$products = $em->getRepository('EstokinPanelBundle:Product')->getForLocalA();

    	return $this->render('EstokinPanelBundle:Item:todolist-a.html.twig', array('items' => $items, 'products' => $products, 'attrset' => $attrset));
    }
    
    /**
     * Sell an existing Item entity.
     *
     * @Route("/item/sell", name="item_sell")
     * @Method("post")
     */
    public function sellAction()
    {    
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
    
    	$request = $this->getRequest();
    
    	$id = $request->get('product');
    	$quantity = $request->get('quantity');
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Item entity.');
    	}
    
    	//No AJAX case (des de online, index tab-2, online product)
    	else{
    
    		if($entity->sellItem($quantity)){
	    		
	    		try{
	
	    			$em->persist($entity);
	    			$order = new Order($entity,$quantity);
	    
	    			$em->persist($order);
	    
	    			$msg = "Le notificamos que se ha vendido el producto '".$entity->getProduct()->getBigName()."'.\nEn breves se procederá a la recogida del producto. Si hay algún inconveniente, por favor, infórmenos cuanto antes.\nGracias";
	    			$this->notifier($entity->getShop(), $msg, 1, $em);

	    			$arr = $this->get('session')->getFlashes();
		            $arr['good'][] = "El item: ".$entity->getCompleteName()." ha sido marcado como vendido.";
		            $arr['good'][] = "Se ha creado el pedido correspondiente al item: ".$entity->getCompleteName()." con ".$quantity." unidades.";
		            $this->get('session')->setFlashes($arr);
		            
		            $em->flush();
	    			 
	    			return $this->redirect($this->generateUrl('order_pending'));
	    		}
	    		catch(Exception $e){
		    		
	    		}
    		}
    		else {
	    		$arr = $this->get('session')->getFlashes();
		        $arr['bad'][] = "No hay suficiente stock del item: ".$entity->getCompleteName()." para realizar el pedido";
		        $this->get('session')->setFlashes($arr);
    			
    			$errors[] = "No hay suficiente stock para realizar el pedido";
    			$entities = $em->getRepository('EstokinPanelBundle:Item')->getForStockA();
    			return $this->render('EstokinPanelBundle:Item:stock-a.html.twig', array(
    					'entities' => $entities,
    					'errors' => $errors
    			));
    		}
    	}
    }    
    
    /**
     * set stock of existing Item entity.
     *
     * @Route("/item/setstock", name="item_set_stock")
     * @Method("post")
     */
    public function setStockAction(){
    
    	$request = $this->getRequest();
    	$id = $request->get('id');
    	$stock = $request->get('quantity');
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
    	$ps_id = $entity->getPSId();
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Product entity.');
    	}


    	if($entity->getState() == 'STATE_ONLINE'){

	    	$message = "
	    		La tienda ".$entity->getShop()->getName()." ha actualizado el stock del producto ".$entity->getName()." en ".$stock." unidades. 
	    	";
	    	if(!mail('roctoll@gmail.com', 'Actualización stock', $message)){
        		$msg = "La operación del producto -".$entity->getProduct()->getBigName()."- ha fallado al modificar stock en PRESTASHOP";
	    		$this->notifier($entity->getShop(), $msg, false, $em);
				$arr = $this->get('session')->getFlashes();
				$arr['bad'][] = "No se ha podido actualizar el stock del item '".$entity->getCompleteName()."' debido a un error de comunicación con Prestashop.";
				$this->get('session')->setFlashes($arr);	
				$em->flush();
				return $this->redirect($this->generateUrl('item_list'));
			} 
/*
			$uploader = $this->get('uploader');
			$arr = $this->get('session');   
    		if($uploader->PS_setStock_item($ps_id, $stock, $arr)){
	    		$msg = "El stock del producto -".$entity->getProduct()->getBigName()."- ha sido incrementado en ".$stock." unidades en PRESTASHOP";
	    		$this->notifier($entity->getShop(), $msg, false, $em);
    		}
        	else {
        		$msg = "La operación del producto -".$entity->getProduct()->getBigName()."- ha fallado al modificar stock en PRESTASHOP";
	    		$this->notifier($entity->getShop(), $msg, false, $em);
				$arr = $this->get('session')->getFlashes();
				$arr['bad'][] = "No se ha podido actualizar el stock del item '".$entity->getCompleteName()."' debido a un error de comunicación con Prestashop.";
				$this->get('session')->setFlashes($arr);	
				$em->flush();
				return $this->redirect($this->generateUrl('item_list'));
			}		
*/
    	}
    	
    	try{
			$entity->setStock($stock);
			$em->persist($entity);
			$msg = "El stock del producto -".$entity->getProduct()->getBigName()."- ha sido incrementado en ".$stock." unidades";
			$this->notifier($entity->getShop(), $msg, false, $em);
			$em->flush();
			
			$arr = $this->get('session')->getFlashes();
	    	$arr['good'][] = "Se ha actualizado el stock del item '".$entity->getCompleteName()."' a ".$stock." unidades.";
			$this->get('session')->setFlashes($arr);
			
			return $this->redirect($this->generateUrl('item_list'));
		}
		catch(Exception $e) {
			$arr = $this->get('session')->getFlashes();
	    	$arr['bad'][] = "No se ha podido actualizar el stock del item '".$entity->getCompleteName()."' debido a un error de persistencia.";
			$this->get('session')->setFlashes($arr);
			
			return $this->redirect($this->generateUrl('item_list'));
		}    	    	 
    }
    
     /**
     * Mark the Item as uploaded.
     *
     * @Route("/item/{id}/upload", name="item_upload")
     * @Method("post")
     */
    public function uploadAction($id){
    
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
	    
	    $request = $this->getRequest();
    	$psid = $request->get('psid');    
	     
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Item entity.');
    	}

   		if($entity->uploadItem($psid)){
    	       	   
    		$em->persist($entity);
    		$em->flush();
    
    		$arr = $this->get('session')->getFlashes();
	    	$arr['good'][] = "Se ha marcado el item '".$entity->getCompleteName()."' como online con PS-id: ".$psid;
			$this->get('session')->setFlashes($arr);
    		return $this->redirect($this->generateUrl('item_todolist'));
    	}
    	else return false;
    }

    
    public function upload_item($id, $psid){
    
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Item')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Item entity.');
    	}

   		if($entity->uploadItem($psid)){
    	       	   
    		$em->persist($entity);
    		$em->flush();
    
    		return $psid;
    	}
    	else return false;
    }
    
    /**
     * UPload file page.
     *
     * @Route("/item/uploadfilepage", name="item_uploadfile_page")
     * @Template()
     */
    public function UploadFilePageAction()
    {
        return  $this->render('EstokinPanelBundle:Item:upload.html.twig');
    }    
     /**
     * Upload and send a file.
     *
     * @Route("/item/uploadfile", name="item_uploadfile")
     * @Method("post")
     */
    public function uploadFileAction(){
	    
	    $request = $this->getRequest();
        $user = $this->get("security.context")->getToken()->getUser();
	    
	    $sPara = "roc@estokin.com";
	    $sAsunto = "[EstokinPanel] Nuevo fichero datos productos";
	    $sTexto = $request->get('msg');
	    $sDe = $user->getEmail();
    	$bHayFicheros = 0;
		$sCabeceraTexto = "";
		$sAdjuntos = "";
		 
		if ($sDe)$sCabeceras = "From:".$sDe."\n";
		else $sCabeceras = "";
		$sCabeceras .= "MIME-version: 1.0\n";
		foreach ($_POST as $sNombre => $sValor)
		$sTexto = $sTexto."\n".$sNombre." = ".$sValor;
		 
		foreach ($_FILES as $vAdjunto)
		{
		if ($bHayFicheros == 0)
		{
		$bHayFicheros = 1;
		$sCabeceras .= "Content-type: multipart/mixed;";
		$sCabeceras .= "boundary=\"--_Separador-de-mensajes_--\"\n";
		 
		$sCabeceraTexto = "----_Separador-de-mensajes_--\n";
		$sCabeceraTexto .= "Content-type: text/plain;charset=iso-8859-1\n";
		$sCabeceraTexto .= "Content-transfer-encoding: 7BIT\n";
		 
		$sTexto = $sCabeceraTexto.$sTexto;
		}
		if ($vAdjunto["size"] > 0)
		{
		$sAdjuntos .= "\n\n----_Separador-de-mensajes_--\n";
		$sAdjuntos .= "Content-type: ".$vAdjunto["type"].";name=\"".$vAdjunto["name"]."\"\n";;
		$sAdjuntos .= "Content-Transfer-Encoding: BASE64\n";
		$sAdjuntos .= "Content-disposition: attachment;filename=\"".$vAdjunto["name"]."\"\n\n";
		 
		$oFichero = fopen($vAdjunto["tmp_name"], 'r');
		$sContenido = fread($oFichero, filesize($vAdjunto["tmp_name"]));
		$sAdjuntos .= chunk_split(base64_encode($sContenido));
		fclose($oFichero);
		}
		}
		 
		if ($bHayFicheros)
		$sTexto .= $sAdjuntos."\n\n----_Separador-de-mensajes_----\n";
		
		if(mail($sPara, $sAsunto, $sTexto, $sCabeceras)){
		    $arr = $this->get('session')->getFlashes();
            $arr['good'][] = "El fichero se ha enviado con éxito";
            $this->get('session')->setFlashes($arr);
            return $this->redirect($this->generateUrl('item_uploadfile_page'));
		}
		else{
		    $arr = $this->get('session')->getFlashes();
            $arr['bad'][] = "El fichero no se ha enviado con éxito, vuélvelo a intentar o ponte en contacto con el administrador";
            $this->get('session')->setFlashes($arr);
            return $this->redirect($this->generateUrl('item_uploadfile_page'));			
		}
    }
    
    /**
     * catch the PS hook.
     *
     * @Route("/wservice/pshook", name="pshook")
     * @Method("post")
     */
    public function psHookAction(){

    	$request = $this->getRequest();
    	 
    	$products = json_decode($request->getContent(), true);
    
    	$em = $this->getDoctrine()->getEntityManager();
    	
    	if($products['key'] != 'jDE89dnkJkn?2mJ21ds') return $this->returnJSON("You don't have access to this action", 401);

    	foreach ($products['prods'] as $prod){

    		$entity = $em->getRepository('EstokinPanelBundle:Item')->findOneBy(array ('PS_id' => $prod['item']));
    		if($entity && $entity->sellItem($prod['quantity'])){

    			$order = new Order($entity,$prod['quantity']);
     
    			$em->persist($order);
    			$em->persist($entity);
    			    			 
    			$msg = "Le notificamos que se ha vendido el producto '".$entity->getProduct()->getBigName()."' con referencia '".$entity->getReference()."'.\nEn breves se procederá a la recogida del producto.\nGracias";
    			$this->notifier($entity->getShop(), $msg, true, $em);
    		}
    		else{
	    		$msg = "Le notificamos que se ha habido un problema con la creación del pedido para la venta del producto '".$entity->getProduct()->getBigName()."' con referencia '".$entity->getReference();
    			$this->notifier($entity->getShop(), $msg, false, $em);
    		}
    
    	}
    	$em->flush();
    	
    	return $this->returnJSON('success');
    }


    
    /**
     * Creates a notification.
     *
     * @param shop			$shop
     * @param string		$content
     * @param boolean 		$admin
     * @param entityManager $em
     */
    private function notifier($shop, $content, $admin, $em){
    	$msg = new Message('NOTIFICATION');
    	$msg->setShop($shop);
    	$msg->setSubject("Notificación de venta");
    	$msg->setContent($content);
    	$msg->setPriority(5);
    	$msg->setAdmin($admin);
    	 
    	$em->persist($msg);
    	//$em->flush();
    }
    
    /**
     * Returns a JSON response.
     *
     * @param array $content
     * @param integer $code
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function returnJSON($content, $code = 200)
    {
    	$response = new Response();
    
    	$response->setContent(json_encode($content));
    	$response->setStatusCode($code);
    	$response->headers->set('Content-Type', 'application/json');
    
    	return $response;
    }
    
    //Sort the categories by levels, for the select field
    public function doTree($clist){
	    $ctree = array();
	    $i = 0;
	    foreach($clist as $cat){
		    $ctree[$i]['name']=$cat->getName();
		    $ctree[$i]['id']=$cat->getId();
		    $ii = 0;
		    foreach($cat->getCategories() as $ccat){
			    $ctree[$i]['cats'][$ii]['name']=$ccat->getName();
			    $ctree[$i]['cats'][$ii]['id']=$ccat->getId();			    
			    $ii++;
		    }
		    $i++;
	    } 
	    return $ctree;
    }

    //Classify the products for the front page
/*
    private function classItems($entities){
    
    	$enties['pendents'] = null;
    	$enties['online'] = null;
    	foreach($entities as $entity){
    		switch ($entity->getProduct()->getState()){
    			case 'STATE_RAW':
    				$enties['pendents'][] = $entity;
    				break;
    			case 'STATE_VALID':
    				$enties['pendents'][] = $entity;
    				break;
    			case 'STATE_ONLINE':
    				$enties['online'][] = $entity;
    				break;
    		}
    	}
    	return $enties;
    }
*/

}













