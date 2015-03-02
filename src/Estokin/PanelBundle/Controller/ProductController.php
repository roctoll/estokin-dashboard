<?php

namespace Estokin\PanelBundle\Controller;

use Estokin\PanelBundle\Entity\Order;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\Product;
use Estokin\PanelBundle\Entity\Message;
use Estokin\PanelBundle\Form\ProductType;


/**
 * Product controller.
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
        
    /**
     * Lists all Product entities.
     *
     * @Route("/", name="product")
     * @Template()
     */
    public function indexAction()
    {
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
	    
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();

    	$entities = $em->getRepository('EstokinPanelBundle:Product')->findAll();
    	
    	return array(
    		'entities'	=> $entities	
    	);
    }
           
    /**
     * Finds and displays a Product entity.
     *
     * @Route("/{id}/show", name="product_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Product entity.
     *
     * @Route("/new", name="product_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Product();
        $user = $this->get("security.context")->getToken()->getUser();
        $form   = $this->createForm(new ProductType(), $entity);
        
        $document = new Document();
	    $docForm = $this->createFormBuilder($document)
	        ->add('name')
	        ->add('file')
	        ->getForm();	        

        //admin case
        if(!$user->isShop()) return $this->render('EstokinPanelBundle:Product:new-a.html.twig', array(
        		'entitiy' => $entity, 
        		'user' => $user, 
        		'form' => $form->createView(),
/*         		'docForm' => $docForm->createView() */
        		));
        
        //normal case
        return array(
        	'user' => $user,
            'entity' => $entity,
            'form'   => $form->createView(),
/*             'docForm' => $docForm->createView() */
        );
    }

    /**
     * Creates a new Product entity.
     *
     * @Route("/create", name="product_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:Product:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Product();
        $errors = array();
        
        $request = $this->getRequest();
        $form    = $this->createForm(new ProductType(), $entity);
        $form->bindRequest($request);
        $shop = $this->get("security.context")->getToken()->getUser();
        if($shop->isShop()){
        	$entity->setShop($shop);
        	$entity->setState('STATE_RAW');
        } 
        
        if ($form->isValid()) {
        	
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
			
            $arr = $this->get('session')->getFlashes();
    	    $arr['good'][] = "El producto: ".$entity->getName()." ha sido creado con éxito.";
			$this->get('session')->setFlashes($arr);
			
            return $this->redirect($this->generateUrl('product_show', array('id' => $entity->getId())));   
        }
        
        $arr = $this->get('session')->getFlashes();
    	$arr['bad'][] = "El producto: ".$entity->getName()." no ha sido creado con éxito.";
		$this->get('session')->setFlashes($arr);
        
        foreach($form->getErrors() as $error)
        {	
             $errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }
        	
        return array(
        	'errors' => $errors,
            'entity' => $entity,
        	'user' 	 => $shop,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }
        if ($entity->getState() != 'STATE_RAW' && $entity->getState() != 'STATE_VALID'){
        	throw new \Exception('No se puede editar este elemento');
        }

        $editForm = $this->createForm(new ProductType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $user = $this->get("security.context")->getToken()->getUser();
        
        //admin case
        if(!$user->isShop()) return $this->render('EstokinPanelBundle:Product:edit-a.html.twig', array(
        		'entity' => $entity,
        		'edit_form'   => $editForm->createView(),
            	'delete_form' => $deleteForm->createView(),
        ));
        
        //normal case
        return array(
            'entity' => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Product entity.
     *
     * @Route("/{id}/update", name="product_update")
     * @Method("post")
     * @Template("EstokinPanelBundle:Product:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $editForm   = $this->createForm(new ProductType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);
        
        //check and change the product state to "RAW_STATE"
        if(!$entity->editProduct()){
        	throw $this->createNotFoundException('Unable to edit a no-local product');
        }

        if ($editForm->isValid()) {
        
	        if($_FILES['estokin_panelbundle_producttype']['name']['image'] != ""){	        	
	        	$file = $editForm['image']->getData();
	        	$dir = __DIR__ . '/../../../../web/uploads/images';
	        	$extension = $file->guessExtension();
				if (!$extension) {
				    // extension cannot be guessed
				    $extension = 'bin';
				}
				$file->move($dir, 'image-'.preg_replace('[\s+]', '_', $entity->getName()).'.'.$extension);				
				$entity->setImage('image-'.preg_replace('[\s+]', '_', $entity->getName()).'.'.$extension);
			}

        
            $em->persist($entity);
            $em->flush();

            $arr = $this->get('session')->getFlashes();
    	    $arr['good'][] = "El producto: ".$entity->getName()." ha sido actualizado.";
			$this->get('session')->setFlashes($arr);
            return $this->redirect($this->generateUrl('product_show',array('id'=>$entity->getId())));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Product entity.
     *
     * @Route("/{id}/delete", name="product_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {              	
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }

        $request = $this->getRequest();
        
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);
        if (!$entity) {
        	throw $this->createNotFoundException('Unable to find Product entity.');
        }

        //If the product has related items, it can't be removed
        if($entity->hasItems()){
	        $arr = $this->get('session')->getFlashes();
	        $arr['bad'][] = "El producto '".$entity->getBigName()."' no se puede eliminar porque tiene ítems asociados.";
	        $this->get('session')->setFlashes($arr);
	        
	        if ($request->isXmlHttpRequest()){
	        	return $this->returnJSON(array(
        				'success' => false,
        				'id' => $id,
        				'product' => $entity->getName(),
        				'error_msg' => "El producto '".$entity->getBigName()."' no se puede eliminar porque tiene ítems asociados.",
        		), 200);
	        }
	        return $this->redirect($this->generateUrl('item_todolist'));
        }
        
        if ($request->isXmlHttpRequest()){
        	 
        	try{
        	$em->remove($entity);
        	$em->flush();
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
        				'exception' => $e,
        		), 500);
        	}
        }
        else{
        
	        $form = $this->createDeleteForm($id);
	        $form->bindRequest($request);
	
	        if ($form->isValid()) {

	            $em->remove($entity);
	            $em->flush();
	            
	            return $this->redirect($this->generateUrl('item_todolist'));
	        }
	        foreach($form->getErrors() as $error)
	        {
	        	$errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
	        }
	        return $this->render($this->generateUrl('product_edit'), array(
	        	'errors'	  => $errors,
	            'entity'      => $entity,
	            'edit_form'   => $editForm->createView(),
	            'delete_form' => $deleteForm->createView(),
	        ));
        }
    }
    /**
     * Validate an existing Product entity.
     *
     * @Route("/validate", name="product_validate")
     * @Method("post")
     */
    public function validateAction(){
    
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
    
    	$request = $this->getRequest();
    	if(!$request->isXmlHttpRequest()) return false;
    
    	$id = $request->get('product');
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Product entity.');
    	}
    	$action = $request->get('action'); 

    	if($entity->validateProduct($action)){
    		 
    		$em->persist($entity);
    		$em->flush();
    		 
    		return $this->returnJSON(array(
    				'success' => true,
    				'id' => $id,
    				'product' => $entity->getName(),
    				'state' => $entity->getState(),
    		), 200);
    	}
    	else return $this->returnJSON(array(
    			'success' => false,
    			'id' => $id,
    			'product' => $entity->getName(),
    			'state' => $entity->getState(),
    	), 200);
    }
    /**
     * Upload an valid existing Product entity.
     *
     * @Route("/{id}/upload", name="product_upload")
     * @Method("post")
     */
    public function uploadAction($id){
    
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }

    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Product entity.');
    	}
    	$request = $this->getRequest();
    	$psid = $request->get('psid');
    		
		//$uploader = $this->get('uploader');
		//$psid = $uploader->PS_upload_product($entity);
		
    	if( ($psid) && $entity->uploadProduct($psid)){
    	
    	    //$this->get('session')->getFlashBag()->add('good',"El producto: ".$entity->getBigName()." ha sido subido con éxito."); 
    	    $arr = $this->get('session')->getFlashes();
    	    $arr['good'][] = "El producto: ".$entity->getBigName()." ha sido subido con éxito.";
			$this->get('session')->setFlashes($arr); 
			
/*
    		foreach($entity->getItems() as $item){
    			if($psid_item = $uploader->PS_upload_item($item)){
    				$item->setPSId($psid_item);
    				//$this->get('session')->getFlashes()->add('good',"El item: ".$item->getName()." ha sido subido con éxito.");
    				$arr = $this->get('session')->getFlashes();
		    	    $arr['good'][] = "El item: ".$item->getCompleteName()." ha sido subido con éxito.";
					$this->get('session')->setFlashes($arr);
    			}
    			else{
	    			$arr = $this->get('session')->getFlashes();
		    	    $arr['bad'][] = "El item: ".$item->getCompleteName()." no ha sido subido con éxito.";
					$this->get('session')->setFlashes($arr);
    			}
    		}
*/
    		
    		$em->persist($entity);
    		$em->flush();
    		 
    		return $this->redirect($this->generateUrl('item_todolist'));
    	}
    	else {
    		$arr = $this->get('session')->getFlashes();
    	    $arr['bad'][] = "El producto: ".$entity->getBigName()." no ha sido subido con éxito.";
			$this->get('session')->setFlashes($arr);
			
    		return $this->render($this->generateUrl('item_todolist'));
    	}     	
    }
    
    /**
     * Blocks or Unavailable an existing online Product entity.
     *
     * @Route("/block", name="product_block")
     * @Method("post")
     */
    public function blockAction(){
    	 
    	$request = $this->getRequest();
    	if(!$request->isXmlHttpRequest()) return false;
    
    	$id = $request->get('product');
    	$op = $request->get('option');    	
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);
    	 
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Product entity.');
    	}
    	//choose beetwen block or unav
    	if($op == 1){
    		$act = $entity->unavProduct();
    		$select = $request->get('select');
    		$text = $request->get('text');
    		$msg = "El producto -".$entity->getName()."- ha sido inhabilitado por ".$select.". Incidencia: ".$text;
    		$this->notifier($entity, $msg, false, $em);
    	}
    	else $act = $entity->blockProduct();
    	
    	//connected version
    	if($act && $this->PS_block($entity->getPSId(),0)){ 
    	//if($act){
    		
    		$em->persist($entity);
    		$em->flush();
    		 
    		return $this->returnJSON(array(
    				'success' => true,
    				'id' => $id,
    				'product' => $entity->getName(),
    				'state' => $entity->getState(),
    		), 200);
    	}
    	else return $this->returnJSON(array(
    			'success' => false,
    			'id' => $id,
    			'product' => $entity->getName(),
    			'state' => $entity->getState(),
    	), 200);
    }
    /**
     * Blocks an existing online Product entity.
     *
     * @Route("/unblock", name="product_unblock")
     * @Method("post")
     */
    public function unblockAction(){
    
    	$request = $this->getRequest();
    	if(!$request->isXmlHttpRequest()) return false;
    
    	$id = $request->get('product');
    	$op = $request->get('option');
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Product entity.');
    	}
    	//choose beetwen block or unav
    	if($op == 1) $act = $entity->avProduct();
    	else $act = $entity->unblockProduct();
    	
    	//Connected version
    	if($act && $this->PS_block($entity->getPSId(),1)){
    	//if($act){
    		
    		$em->persist($entity);
    		$em->flush();
    		 
    		return $this->returnJSON(array(
    				'success' => true,
    				'id' => $id,
    				'product' => $entity->getName(),
    				'state' => $entity->getState(),
    		), 200);
    	}
    	else return $this->returnJSON(array(
    			'success' => false,
    			'id' => $id,
    			'product' => $entity->getName(),
    			'state' => $entity->getState(),
    	), 200);
    }
    
    /**
     * Retrieve info of an existing Product entity.
     *
     * @Route("/info", name="product_info")
     * @Method("post")
     */
    public function infoAction(){
    	
    	$request = $this->getRequest();

    	if(!$request->isXmlHttpRequest()) return false;    	
    
    	$id = $request->get('product');
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('EstokinPanelBundle:Product')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Product entity.');
    	}
    	    
    	else{    
    		if($entity->getBrand() != null) $brand = $entity->getBrand()->getId();
    		else $brand = 0;

    		return $this->returnJSON(
   				array(
    		    	'name' => $entity->getName(),
    		    	'code' => $entity->getCode(),
   					'brand'		=> $brand,
   					'category'	=> $entity->getCategory()->getId(),
   					'description'	=> $entity->getDescription(),
   					'image'	=> $entity->getImage(),
   			), 200);    		
    	}
    }
   
    
    private function updateSales($entities, $em){
    	foreach ($entities as $ent){
    		$id = $ent->getPSId();
    		if($id != null && $id != 0){
    			$stock = $this->PS_getStock($id);
    			if($stock < $ent->getStock()) {
    				if($ent->sellProduct()){
    					$order = new Order($ent,1);
    
    					$em->persist($order);
    					$em->persist($ent);
    
    					$msg = "Le notificamos que se ha vendido el producto '".$ent->getName()."' con referencia '".$ent->getReference()."'.\nEn breves se procederá a la recogida del producto.\nGracias";
    					$this->notifier($ent, $msg, true, $em);
    				}
    			}
    		}
    	}
    	$em->flush();
    }
    
    private function updateOrders($em){
    	 
    	$last = $em->getRepository('EstokinPanelBundle:Order')->getLastPSOrder() + 1;
    
    	$orders = $this->PS_getOrders($last);
    	foreach ($orders as $ps_order){
    		$entity = $em->getRepository('EstokinPanelBundle:Product')->findOneBy(array ('PS_id' => $ps_order->product_id));
    
    		if($entity && $entity->sellProduct($ps_order->product_quantity)){
    			$order = new Order($entity,$ps_order->product_quantity, $ps_order->id);
    
    			$em->persist($order);
    			$em->persist($entity);
    
    			$msg = "Le notificamos que se ha vendido el producto '".$entity->getName()."' con referencia '".$entity->getReference()."'.\nEn breves se procederá a la recogida del producto.\nGracias";
    			$this->notifier($entity, $msg, true, $em);
    		}
    	}
    	$em->flush();
    }
    
    private function PS_block($id, $block){

    	$pspath = $this->container->getParameter('PS_SHOP_PATH');
    	$pskey = $this->container->getParameter('PS_WS_AUTH_KEY');
    	try
    	{
    		$webService = new PrestaShopWebservice($pspath, $pskey, false);
    		$opt = array('resource' =>'products');
    		$opt['id'] = $id;
    		$opt['custom'] = 'price[my_price][use_tax]=0';
    		$xml = $webService->get($opt);
    	
    		// Here we get the elements from children of customer markup which is children of prestashop root markup
    		$resources = $xml->children()->children();
    		//XML fails if don't delete this nodes
    		unset($resources->position_in_category); 
			unset($resources->manufacturer_name);
    	
    	}
    	catch (PrestaShopWebserviceException $e)
    	{
    		// Here we are dealing with errors
    		$trace = $e->getTrace();
    		if ($trace[0]['args'][0] == 404) echo 'Bad ID';
    		else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
    		else echo 'Other error';
    	}
    	
    	// Here we have XML before update, lets update XML with new values

    	$resources->active = $block;
    	$resources->price = $resources->my_price; //patch -> default price in GET is not the same as PUT
    	
    	// And call the web service
    	try
    	{
    		$opt = array('resource' => 'products');
    		$opt['putXml'] = $xml->asXML();
    		$opt['id'] = $id;
    		$xml = $webService->edit($opt);
    		// if WebService don't throw an exception the action worked well and we don't show the following message
    		return true;
    	}
    	catch (PrestaShopWebserviceException $ex)
    	{
    		// Here we are dealing with errors
    		$trace = $ex->getTrace();
    		if ($trace[0]['args'][0] == 404) echo 'Bad ID';
    		else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
    		else echo 'Other error<br />'.$ex->getMessage();
    		return false;
    	}
    }
    
 
    
    private function PS_getOrders($id){
    	try
    	{
    		$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
    		$opt = array('resource' => 'order_details',
    					 'display'	=> '[id, product_id, product_quantity]',
    					 'filter[id]'	=> '['.$id.',10000000]');
    		$xml = $webService->get($opt);

    		// Here we get the elements from children of customer markup which is children of prestashop root markup

    		return $xml->children()->children();
    	}
    	catch (PrestaShopWebserviceException $e)
    	{
    		// Here we are dealing with errors
    		$trace = $e->getTrace();
    		if ($trace[0]['args'][0] == 404) echo 'Bad ID';
    		else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
    		else echo 'Other error';
    		return false;
    	}
    }
    
    /**
     * Creates a notification.
     *
     * @param product	$entity 
     * @param string	$content
     * @param boolean 	$admin
     * @param entityManager $em
     */
    private function notifier($entity, $content, $admin, $em){
    	$msg = new Message('NOTIFICATION');
    	$msg->setShop($entity->getShop());
    	$msg->setSubject("Notificación de venta");
    	$msg->setContent($content);
    	$msg->setPriority(5);
    	$msg->setAdmin($admin);
    	
    	$em->persist($msg);
    	$em->flush();
    }
    
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
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

}
