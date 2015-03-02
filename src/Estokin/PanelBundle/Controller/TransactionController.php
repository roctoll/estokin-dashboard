<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\Transaction;
use Estokin\PanelBundle\Entity\Entry;
use Estokin\PanelBundle\Form\TransactionType;


/**
 * transaction controller.
 *
 * @Route("/transaction")
 */
class TransactionController extends Controller
{
	

    /**
     * Displays a form to create a new Transaction entity.
     *
     * @Route("/admin/new", name="transaction_new")
     * @Template()
     */
    public function newAction()
    {
    	if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
        $entity = new Transaction();
        $form   = $this->createForm(new TransactionType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }
    
    /**
     * Creates a new Transaction entity.
     *
     * @Route("/create", name="transaction_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:Transaction:new.html.twig")
     */
    public function createAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
        $entity  = new Transaction();
        $request = $this->getRequest(); 
        $form    = $this->createForm(new TransactionType(), $entity);
        $form->bindRequest($request);
        $amount = 0;
        foreach ($entity->getOrders() as $ord){
	        $amount = $amount + $ord->getTotalAmount();
	        $ord->setPaid(1);
	        $em->persist($ord);
        }
        $entity->setAmount($amount);     
	    
        if ($form->isValid()) {
            
          try{
            $em->persist($entity); 
            $em->flush();

            $arr = $this->get('session')->getFlashes();
            $arr['good'][] = "Se ha creado el pago a la tienda ".$entity->getShop()." por valor de ".$entity->getAmount()." Euros.";
            $this->get('session')->setFlashes($arr);
            
            return $this->redirect($this->generateUrl('transaction_pending'));
          }
          catch(\Exception $e){
	        $arr = $this->get('session')->getFlashes();
            $arr['bad'][] = "Se ha producido un error en la BBDD";
            $arr['bad'][] = $e->getMessage();
            $this->get('session')->setFlashes($arr);
            return array(
        		'entity' => $entity,
        		'form'   => $form->createView()
        	);
          }
        }
        
        $arr = $this->get('session')->getFlashes();
            
        foreach($form->getErrors() as $error)
        {
        	$errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        	$arr['bad'][] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }
        $this->get('session')->setFlashes($arr);

        return array(
        		'errors' => $errors,
        		'entity' => $entity,
        		'form'   => $form->createView()
        );
    }
        	
	/**
	 * Finds and displays a transaction entity.
	 *
	 * @Route("/{id}/show", name="transaction_show")
	 * @Template()
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
	
		$entity = $em->getRepository('EstokinPanelBundle:transaction')->find($id);
		
		//restricted access to other user's items
		$user = $this->get("security.context")->getToken()->getUser();
		
		if($user->isShop() && $entity->getShop() != $user){
			throw $this->createNotFoundException('No acces to this item.');
		}
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find transaction entity.');
		}
		
		return array(
			'entity' => $entity,
		);
	}
	
	
	
	/**
	 * Lists pending transaction entities.
	 *
	 * @Route("/pending", name="transaction_pending")
	 * @Template()
	 */
	public function pendingTransactionAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		
		if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
			$entities = $em->getRepository('EstokinPanelBundle:transaction')->findByState('STATE_PENDENT');

			return $this->render('EstokinPanelBundle:transaction:pending.html.twig', array('entities' => $entities));
		}
		
		$user = $this->get("security.context")->getToken()->getUser();
		
		$entity = $em->getRepository('EstokinPanelBundle:transaction')->findOneBy(
					array('shop' => $user->getId(), 'state' => 'STATE_PENDENT'),
					array('date_add' => 'DESC')
				); 
								
		return $this->render('EstokinPanelBundle:transaction:show.html.twig', array('entity' => $entity, 'user' => $user));
	}
	/**
	 * Lists Historic done transactions entities.
	 *
	 * @Route("/history", name="transaction_history")
	 * @Template()
	 */
	public function historyTransactionAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get("security.context")->getToken()->getUser();
		if(!$user->isShop()) {
			$entities = $em->getRepository('EstokinPanelBundle:transaction')->findByState('STATE_DONE');

			return $this->render('EstokinPanelBundle:transaction:history.html.twig', array('entities' => $entities));
		}
		$entities = $em->getRepository('EstokinPanelBundle:transaction')->findBy(
					array('shop' => $user->getId(), 'state' => 'STATE_DONE'),
					array('date_upd' => 'ASC')
				);
		return $this->render('EstokinPanelBundle:transaction:history.html.twig', array('entities' => $entities, 'user' => $user));
	}


	/**
	 * Return an existing sold Product entity.
	 *
	 * @Route("/cancel", name="transaction_cancel")
	 * @Method("post")
	 */
	public function cancelAction(){
		
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest()) return false;
	
		$id = $request->get('transaction');
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:transaction')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find transaction entity.');
		}
	
		if($entity->canceltransaction()){
	
			$em->persist($entity);
			$em->flush();
	
			return $this->returnJSON(array(
					'success' => true,
					'id' => $id,
					'transaction' => $entity->getProduct()->getName(),
					'state' => $entity->getState(),
			), 200);
		}
		else return $this->returnJSON(array(
				'success' => false,
				'id' => $id,
				'transaction' => $entity->getProduct()->getName(),
				'state' => $entity->getState(),
		), 200);
		
	}

	/**
	 * Finish an existing transaction entity.
	 *
	 * @Route("/done", name="transaction_done")
	 * @Method("post")
	 */
	public function doneAction(){
	
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest()) return false;
	
		$id = $request->get('id'); 
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:transaction')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find transaction entity.');
		}
		if($entity->endtransaction()){
			 
			//Create new transaction
			$em = $this->getDoctrine()->getEntityManager();
			$new = new Transaction();
			$new->setShop($entity->getShop());
			 
			$em->persist($new); 
			$em->persist($entity);
			$em->flush();
			 
			return $this->returnJSON(array(
					'success' => true,
					'id' => $id,
					'state' => $entity->getState(),
			), 200);
		}
		else return $this->returnJSON(array(
				'success' => false,
				'id' => $id,
				'transaction' => $entity->getProduct()->getName(),
				'state' => $entity->getState(),
		), 200);
	}
	
	/**
	 * Add an entry to an existing transaction entity.
	 *
	 * @Route("/addentry", name="transaction_addentry")
	 * @Method("post")
	 */
	public function addentryAction(){
	    
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
	    
		$request = $this->getRequest();
	
		$id = $request->get('id'); 
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:Transaction')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find transaction entity.');
		}
		
		try{
			$entry_concept = $request->get('concept');
			$entry_value = $request->get('value');
			
			$entry = new Entry($entry_concept, $entry_value);
			$entity->addEntry($entry);
			
			$em->persist($entry);
			$em->persist($entity);
			$em->flush();
			 
			return $this->redirect($this->generateUrl('transaction_show', array(
				'id' => $id,
			))); 
			
		}catch(\Exception $e){
			return $this->redirect($this->generateUrl('transaction_show', array(
				'errors' => $e->getMessage(),
				'id' => $id,
			))); 
		}

	}

	/**
	 * Return an existing sold Product entity.
	 *
	 * @Route("/updateamount", name="transaction_updateAmounts")
	 * @Method("post")
	 */
	public function UpdateAmountAction(){
		
		if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new AccessDeniedException();
	    }
	    
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest()) return false;
	
		$em = $this->getDoctrine()->getEntityManager();
		$entities = $em->getRepository('EstokinPanelBundle:transaction')->findByState('STATE_PENDENT');
	
		try{
			foreach($entities as $trans){
				$trans->setAmount();
				$em->persist($trans);
			}		
			$em->flush();		
			return $this->returnJSON(array(
					'success' => true,
			), 200);
		}
		catch(\Exception $e){ 
			return $this->returnJSON(array(
				'success' => false,
				'error' => $e->getMessage(),
			), 500);
		}
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