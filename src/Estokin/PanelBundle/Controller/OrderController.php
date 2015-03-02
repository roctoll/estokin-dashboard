<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\Product;
use Estokin\PanelBundle\Entity\Transaction;


/**
 * Order controller.
 *
 * @Route("/order")
 */
class OrderController extends Controller
{
	
	/**
	 * Lists sold Product entities.
	 *
	 * @Route("/", name="orders")
	 * @Template()
	 */
	public function soldProductsAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get("security.context")->getToken()->getUser();
		if(!$user->isShop()) {
			$entities = $em->getRepository('EstokinPanelBundle:Order')->findAll();
	
			return $this->render('EstokinPanelBundle:Product:sold-a.html.twig', array('entities' => $entities));
		}
		$entities = $em->getRepository('EstokinPanelBundle:Order')->findBy(
														array('shop' => $user->getId()),
														array('state' => 'DESC'));
				
		return $this->render('EstokinPanelBundle:Product:sold.html.twig', array('entities' => $entities, 'user' => $user));
	}
	
	
	/**
	 * Finds and displays a Order entity.
	 *
	 * @Route("/{id}/show", name="order_show")
	 * @Template()
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
	
		$entity = $em->getRepository('EstokinPanelBundle:Order')->find($id);
		
		//restricted access to other user's items
		$user = $this->get("security.context")->getToken()->getUser();
		
		if($user->isShop() && $entity->getShop() != $user){
			throw $this->createNotFoundException('No acces to this item.');
		}
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Order entity.');
		}
	
		//$deleteForm = $this->createDeleteForm($id);
	
		return array(
				'entity'      => $entity,
				//'delete_form' => $deleteForm->createView(),        
				);
	}
	
	
	/**
	 * Lists pending order entities.
	 *
	 * @Route("/pending", name="order_pending")
	 * @Template()
	 */
	public function pendingOrderAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get("security.context")->getToken()->getUser();
		if(!$user->isShop()) {
			$entities = $em->getRepository('EstokinPanelBundle:Order')->findByState('STATE_SOLD');
	
			return $this->render('EstokinPanelBundle:Order:pending-a.html.twig', array('entities' => $entities));
		}
		$entities = $em->getRepository('EstokinPanelBundle:Order')->findBy(
					array('shop' => $user->getId(), 'state' => 'STATE_SOLD'),
					array('date_sold' => 'ASC')
				);
		return $this->render('EstokinPanelBundle:Order:pending.html.twig', array('entities' => $entities, 'user' => $user));
	}
	/**
	 * Lists Historic done orders entities.
	 *
	 * @Route("/history", name="order_history")
	 * @Template()
	 */
	public function historyOrderAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get("security.context")->getToken()->getUser();
		if(!$user->isShop()) {
			$entities = $em->getRepository('EstokinPanelBundle:Order')->findByState(array('STATE_SENT','STATE_DONE', 'STATE_CANCELED'));
	
			return $this->render('EstokinPanelBundle:Order:history.html.twig', array('entities' => $entities));
		}
		$entities = $em->getRepository('EstokinPanelBundle:Order')->findBy(
				array('shop' => $user->getId(), 'state' => array('STATE_SENT','STATE_DONE', 'STATE_CANCELED')),
				array('date_sold' => 'ASC')
		);
		return $this->render('EstokinPanelBundle:Order:history.html.twig', array('entities' => $entities, 'user' => $user));
	}

	/**
	 * Sell an existing sold Product entity.
	 *
	 * @Route("/sell", name="order_sell")
	 * @Method("post")
	 */
	public function sellAction(){
	
		$request = $this->getRequest();
	
		$id = $request->get('order');
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:Order')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Order entity.');
		}
		//AJAX case (des de vendidos, index tab-3, prodcute en transit o final)
		if($request->isXmlHttpRequest()){
	
			if($entity->sellOrder()){
	
				$em->persist($entity);
				$em->flush();
	
				return $this->returnJSON(array(
						'success' => true,
						'id' => $id,
						'order' => $entity->getProduct()->getName(),
						'state' => $entity->getState(),
				), 200);
			}
			else return $this->returnJSON(array(
					'success' => false,
					'id' => $id,
					'order' => $entity->getProduct()->getName(),
					'state' => $entity->getState(),
			), 200);
		}
	}
	/**
	 * Return an existing sold Product entity.
	 *
	 * @Route("/cancel", name="order_cancel")
	 * @Method("post")
	 */
	public function cancelAction(){
		
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest()) return false;
	
		$id = $request->get('order');
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:Order')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Order entity.');
		}
	
		if($entity->cancelOrder()){
	
			$em->persist($entity);
			$em->flush();
	
			return $this->returnJSON(array(
					'success' => true,
					'id' => $id,
					'order' => $entity->getProduct()->getName(),
					'state' => $entity->getState(),
			), 200);
		}
		else return $this->returnJSON(array(
				'success' => false,
				'id' => $id,
				'order' => $entity->getProduct()->getName(),
				'state' => $entity->getState(),
		), 200);
		
	}
	/**
	 * Send an existing sold Order entity.
	 *
	 * @Route("/send", name="order_send")
	 * @Method("post")
	 */
	public function sendAction(){
	
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest()) return false;
	
		$id = $request->get('order');
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:Order')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Order entity.');
		}
		if($entity->sendOrder()){
			$transaction = $em->getRepository('EstokinPanelBundle:transaction')->findOneBy(
					array('shop' => $entity->getItem()->getShop()->getId(), 'state' => 'STATE_PENDENT'),
					array('date_add' => 'DESC')
				);
			if(!$transaction){
				$transaction = new Transaction();
				$transaction->setShop($entity->getShop());
			}
			$transaction->addOrder($entity);
			 
			$em->persist($transaction); 
			$em->persist($entity);
			$em->flush();
			 
			return $this->returnJSON(array(
					'success' => true,
					'id' => $id,
					'order' => $entity->getItem()->getName(),
					'state' => $entity->getState(),
			), 200);
		}
		else return $this->returnJSON(array(
				'success' => false,
				'id' => $id,
				'order' => $entity->getItem()->getName(),
				'state' => $entity->getState(),
		), 200);
	}
	/**
	 * Finish an existing Order entity.
	 *
	 * @Route("/done", name="order_done")
	 * @Method("post")
	 */
	public function doneAction(){
	
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest()) return false;
	
		$id = $request->get('order');
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('EstokinPanelBundle:Order')->find($id);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Order entity.');
		}
		if($entity->endOrder()){
			 
			$em->persist($entity);
			$em->flush();
			 
			return $this->returnJSON(array(
					'success' => true,
					'id' => $id,
					'order' => $entity->getItem()->getName(),
					'state' => $entity->getState(),
			), 200);
		}
		else return $this->returnJSON(array(
				'success' => false,
				'id' => $id,
				'order' => $entity->getProduct()->getName(),
				'state' => $entity->getState(),
		), 200);
	}
	
	/**
	 * Get sold order array.
	 *
	 * @Route("/stadistics", name="order_stadistics")
	 * @Method("get")
	 */
	public function stadisticsAction(){
		$num = $this->getRequest()->get('num');
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get("security.context")->getToken()->getUser();
		if(!$user->isShop()) {
			$entities = $em->getRepository('EstokinPanelBundle:Order')->findAll(
					array('date_sold' => 'ASC'));
		}
		else{
			$entities = $em->getRepository('EstokinPanelBundle:Order')->findBy(
					array('shop' => $user->getId()),
					array('date_sold' => 'ASC'));
		}
		$entities_vect = $this->classOrdersMonth($entities, $num);
		 
		return $this->render('EstokinPanelBundle:Product:stadistics.html.twig',
				array('entities' => $entities_vect, 'user' => $user, 'num_entities' => count($entities)));
	}
	
	private function classOrdersMonth($entities, $num){
		$enties = Array();
		$tim =  new \DateTime('F Y');
		for($i=0; $i<$num; $i++){
			$enties[$tim->format('F Y')] = Array('name' => $tim->format('F Y'), 'num' => 0, 'cash' => 0);
			$tim->sub(new \DateInterval('P1M'));
		}
		 
		foreach($entities as $entity){
			$tim = $entity->getDateSold()->format('F Y');
			if(array_key_exists($tim, $enties)) {
				$enties[$tim]['num']++;
				$enties[$tim]['cash'] += $entity->getItem()->getProduct()->getPrice();
			}
		}
		$enties = array_reverse($enties);
		//echo('<pre>');print_r($enties);echo('</pre>');die();
		return $enties;
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