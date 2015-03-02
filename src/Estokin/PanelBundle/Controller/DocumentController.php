<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Estokin\PanelBundle\Entity\Document;
		
/**
 * Document controller.
 *
 * @Route("/document")
 */
class DocumentController extends Controller
{
	 /**
     * Displays a form to create a new Document entity.
     *
     * @Route("/upload", name="document_upload")
     * @Template()
     */
	public function uploadAction()
	{
/*
	    $document = new Document();
	    $form = $this->createFormBuilder($document)
	        ->add('name')
	        ->add('file')
	        ->getForm()
	    ;
	
	    $request = $this->getRequest();
	    
	    if ($request->isXmlHttpRequest()){
	    
        	$form->bindRequest($this->getRequest());
        	try{
        		if ($form->isValid()) {
		            $em = $this->getDoctrine()->getEntityManager();
		            
		            $document->upload();
		
		            $em->persist($document);
		            $em->flush();
		
		            return $this->returnJSON(array(
        				'success' => true,
        		), 200);
        		}
        		
    		return $this->returnJSON(array(
    				'success' => false,
    				'error' => 'form not valid',
    		), 403);
    		
        	}catch (Exception $e){
        		return $this->returnJSON(array(
        				'success' => false,
        				'exception' => $e,
        		), 500);
        	}
        }
	
	    if ($this->getRequest()->getMethod() === 'POST') {
	        $form->bindRequest($this->getRequest());
	        if ($form->isValid()) {
	            $em = $this->getDoctrine()->getEntityManager();
	            
	            $document->upload();
	
	            $em->persist($document);
	            $em->flush();
	
	            return $this->redirect($this->generateUrl('item'));
	        }
	    }
	
	    return array('form' => $form->createView());
*/



		
	}
		
    /**
     * Lists all Document entities.
     *
     * @Route("/", name="docs")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Total orders
        $documents = $em->getRepository('EstokinPanelBundle:Document')->findAll();
       
                
        return array('docs' => $documents);
    }

    /**
     * Lists all Document entities.
     *
     * @Route("/new", name="document_new")
     * @Template()
     */
    public function newAction()
    {
	    return array();
    }

    
     /**
     * Show Document entity.
     *
     * @Route("/{id}/show", name="document_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $form = $this->createDeleteForm($id);        

        $document = $em->getRepository('EstokinPanelBundle:Document')->find($id);
       
                
        return array('doc' => $document, 'dform' => $form->createView() );
    }

    /**
     * Deletes a Document entity.
     *
     * @Route("/{id}/delete", name="document_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('EstokinPanelBundle:Document')->find($id);
            $name = $entity->getName();

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Document entity.');
            }

            unlink($entity->getAbsolutePath());

            $em->remove($entity);
            $em->flush();
                        
            $arr = $this->get('session')->getFlashes();
		    $arr['good'][] = "El documento: ".$name." ha sido eliminado con éxito.";
		    $this->get('session')->setFlashes($arr);
        }
        else{
        
  	       	$arr = $this->get('session')->getFlashes();

        	foreach($form->getErrors() as $error)
        	{
        		$arr['bad'][] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        	}
        	 
		    $this->get('session')->setFlashes($arr);
        }

        return $this->redirect($this->generateUrl('docs'));
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
