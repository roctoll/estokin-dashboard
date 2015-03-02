<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\CsrfProvider\CsrfProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Estokin\PanelBundle\Entity\Message;
use Estokin\PanelBundle\Form\MessageType;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends Controller
{
    /**
     * Lists all Message entities.
     *
     * @Route("/", name="message")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser(); 

        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	$messages = $em->getRepository('EstokinPanelBundle:Message')->findByType('TICKET');
        }
        else {   
        	$messages = $em->getRepository('EstokinPanelBundle:Message')->getTicketsShop($user->getId());
        }
        
        return array(
    			'user' => $user,
    			'messages' => $messages);
    }
    
    /**
     * Lists all Message entities.
     *
     * @Route("/conversation", name="message_conv")
     * @Template()
     */
    public function conversationAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();
    
    	if($user->isShop()){
    		$entities = $em->getRepository('EstokinPanelBundle:Message')->findBy(
    				array('shop' => $user->getId(), 'type' => 'TICKET'),
    				array('date_add' => 'DESC'));
    	}
    	else {
    		$entities = $em->getRepository('EstokinPanelBundle:Message')->findBy(
    				array('type' => 'TICKET'),
    				array('date_add' => 'DESC'));
    	}
    	return array('entities' => $entities, 'user' => $user);
    }
    
    
    /**
     * Lists all Message entities.
     *
     * @Route("/notifications", name="notifications")
     * @Template()
     */
    public function notificationsAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();
    	
    	$enti_new = null;
    	$enti_old = null;
    	
    	if($user->isShop()){
    		$entities = $em->getRepository('EstokinPanelBundle:Message')->findBy(
    				array('shop' => $user->getId(), 'type' => 'NOTIFICATION', 'admin' => 1),
    				array('date_add' => 'DESC'));
    		//Mark all notifications as read, for the next time.
    		foreach($entities as $e){
    			if($e->getIsRead() == 1){
    				$e->setIsRead(0);
    				$em->persist($e);
    				$enti_new[] = $e;
    			}
    			else $enti_old[] = $e;	
    		}
    		$em->flush();
    	}
    	else {
    		$entities = $em->getRepository('EstokinPanelBundle:Message')->findBy(
    				array('type' => 'NOTIFICATION', 'admin' => 0),
    				array('date_add' => 'DESC'));
    		foreach($entities as $e){ 
    			if($e->getIsRead() == 2) {
    				$e->setIsRead(0);
    				$em->persist($e);
    				$enti_new[] = $e; 
    			}
    			else {
    				$enti_old[] = $e; 
    			}
    		$em->flush();
    		}
    	}
    
    	return array('entities_old' => $enti_old, 'entities_new' => $enti_new, 'user' => $user);
    }
    
    /**
     * Lists all Notification entities.
     *
     * @Route("/notificationsall", name="notifications_all")
     * @Template()
     */
    public function notificationsAllAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();
    	 
    	if(!$user->isShop()) {
    		$entities = $em->getRepository('EstokinPanelBundle:Message')->findBy(
    				array('type' => 'NOTIFICATION'),
    				array('date_add' => 'DESC'));
    	}
    
    	return $this->render('EstokinPanelBundle:Message:notifications-all.html.twig',array('entities' => $entities));
    }

    /**
     * Finds and displays a Message entity.
     *
     * @Route("/{id}/show", name="message_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $this->get("security.context")->getToken()->getUser();
        $parent = $em->getRepository('EstokinPanelBundle:Message')->find($id);

        if (!$parent) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }
        
        //if it is not the main message, get it
        if($parent->getMain() == false){
	        $parent = $em->getRepository('EstokinPanelBundle:Message')->find($parent->getParent());
        }
        
        $messages = $parent->getReplies();

        $deleteForm = $this->createDeleteForm($id);

        if($parent->getIsRead() == 1 && $user->isShop()) {  
	        	$parent->setIsRead(0);
		        $em->persist($parent);
		        $em->flush();
	    }
	    else if($parent->getIsRead() == 2 && !$user->isShop()){  
		    $parent->setIsRead(0);
		        $em->persist($parent);
		        $em->flush();
	    }
/*
        foreach($messages as $msg){
	        if(!$msg->getIsRead()) {
	        	$entity->setIsRead(true);
		        $em->persist($entity);
	        } 
        }
*/       
        
        
        return array(
            'parent'	=> $parent,
            'messages'	=> $messages,
        	);
    }

    /**
     * Displays a form to create a new Message entity.
     *
     * @Route("/new", name="message_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Message('TICKET');
        $form   = $this->createForm(new MessageType(), $entity);

        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();


        return array(
        	'user'	 => $user,
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Message entity.
     *
     * @Route("/create", name="message_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:Message:new.html.twig")
     */
    public function createAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity  = new Message('TICKET'); 
        $request = $this->getRequest();
        $form    = $this->createForm(new MessageType(), $entity);
        $form->bindRequest($request);    
        $shop = $this->get("security.context")->getToken()->getUser();

	    //if it's a shop, we link the shop to de msj	            
	    if ($shop->isShop()){    
	        $entity->setShop($shop);
	        $entity->setAdmin(false);
	        $entity->setIsRead(2);
	        $entity->setType('TICKET');
	    }
	    
        if ($form->isValid()) {
            $em->persist($entity); 
            $em->flush();

            $arr = $this->get('session')->getFlashes();
		    $arr['good'][] = "El mensaje se ha enviado con éxito.";
		    $this->get('session')->setFlashes($arr);
		    
            return $this->redirect($this->generateUrl('message', array('id' => $entity->getId())));
            
        }
        foreach($form->getErrors() as $error)
        {
        	$errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }
         
        return array(
        		'errors' => $errors,
        		'entity' => $entity,
        		'form'   => $form->createView()
        );
    }
     /**
     * Reply a Message entity.
     *
     * @Route("/reply", name="message_reply")
     * @Method("post")
     */
    public function replyAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity  = new Message('TICKET');
        $request = $this->getRequest();
        $shop = $this->get("security.context")->getToken()->getUser();

    	// it's a reply of other message, link with its parent
	    $entity->setMain(false);
	    $parent = $em->getRepository('EstokinPanelBundle:Message')->find($request->get('parent'));

	    $content = $request->get('content');
	    $entity->setContent($content);

	    //if it's a shop, we link the shop to de msj	            
	    if ($shop->isShop()){    
	        $entity->setShop($shop);
	        $entity->setAdmin(false);
	        $parent->setIsRead(2); 
	    }
	    else{
		    $shop = $em->getRepository('EstokinPanelBundle:Shop')->find($request->get('shop'));
		    $entity->setShop($shop);
		    $parent->setIsRead(1);
	    }
	    	    	    
	    try{	
	    	$entity->setParent($parent); 
	    	$em->persist($parent); 
            $em->persist($entity); 
            $em->flush();

            $arr = $this->get('session')->getFlashes();
		    $arr['good'][] = "El mensaje se ha enviado con éxito.";
		    $this->get('session')->setFlashes($arr);
		    
            return $this->redirect($this->generateUrl('message_show', array('id' => $parent->getId())));
        }catch(\Exception $e){
	     	return array(
        		'errors' => $e->getMessage(),
        		'entity' => $entity,
        	);   
        }   
         
    }
    
     /**
     * Displays a form to edit an existing Message entity.
     *
     * @Route("/{id}/edit", name="message_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $editForm = $this->createForm(new MessageType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Message entity.
     *
     * @Route("/{id}/update", name="message_update")
     * @Method("post")
     * @Template("EstokinPanelBundle:Message:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $editForm   = $this->createForm(new MessageType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('message_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Message entity.
     *
     * @Route("/delete", name="message_delete")
     * @Method("post")
     */
    public function deleteAction()
    {
    	$request = $this->getRequest();
    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();
    	$id = $request->get('id'); 
        $entity = $em->getRepository('EstokinPanelBundle:Message')->find($id); 
    	
    	if($user->isShop() && $entity->getShop() != $user) throw $this->createNotFoundException('No acces to this item.');

    	// AJAX case
        if ($request->isXmlHttpRequest()){
        
        	try{        	
				//If it's an Admin message, instead of delete it, it becomes a 'dead' message
				if($user->isShop() && $entity->getMain()){	
					$entity->setType('DEAD');				
					$em->persist($entity);      			
        		} 
        		
        		else {
	        		$em->remove($entity);
	        	}
	        	$em->flush();

        		return $this->returnJSON(array(
        				'success' => true,
        		), 200);
        	}catch (Exception $e){
        		return $this->returnJSON(array(
        				'success' => false,
        		), 500);
        	}
        }
        
        //POST case	
        $form = $this->createFormBuilder(null, array('csrf_protection' => false))->getForm();

        $form->bindRequest($request);
        
        if ($form->isValid()) {

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Message entity.');
            }

            $em->remove($entity);
            $em->flush();
        }
        else { 
	        foreach($form->getErrors() as $error)
	        {
	        	$errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
	        }
	        return $this->redirect($this->generateUrl('message'),$errors);
        }
        if($entity->getType() == "TICKET") return $this->redirect($this->generateUrl('message'));
        else return $this->redirect($this->generateUrl('notifications'));
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
