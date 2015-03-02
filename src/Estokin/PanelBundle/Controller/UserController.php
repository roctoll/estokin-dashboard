<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\User;
use Estokin\PanelBundle\Form\UserType;

/**
 * User controller.
 *
 * @Route("")
 */
class UserController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/user", name="user")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('EstokinPanelBundle:User')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/user/{id}/show", name="user_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/user/new", name="user_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createForm(new UserType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/user/create", name="user_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:User:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new User();
        $request = $this->getRequest();
        $form    = $this->createForm(new UserType(), $entity);
        $form->bindRequest($request);

        // encode the password
        $factory = $this->get('security.encoder_factory');
		$encoder = $factory->getEncoder($entity);
		$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
		$entity->setPassword($password);	
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/user/{id}/edit", name="user_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createForm(new UserType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/user/{id}/update", name="user_update")
     * @Method("post")
     * @Template("EstokinPanelBundle:User:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm   = $this->createForm(new UserType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);
        // encode the password
        $factory = $this->get('security.encoder_factory');		
		$encoder = $factory->getEncoder($entity); //echo 'pass: '.$entity->getPassword()."<br> salt: ".$entity->getSalt()."<br> ";
		$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt()); //echo 'password final: '.$password;
		$entity->setPassword($password);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }


    /**
     * Displays a form to edit an existing User password.
     *
     * @Route("/user/chpassword", name="change_password")
     * @Template()
     */
    public function chpasswordAction()
    {

    	$em = $this->getDoctrine()->getEntityManager();
    	$user = $this->get("security.context")->getToken()->getUser();

    	if(!$user->isShop()) return $this->redirect($this->generateUrl('user_show', array('id' => $user->getId())));
    	    
    	if (!$user) {
    		throw $this->createNotFoundException('Unable to find Shop entity.');
    	}
 
        return $this->render('EstokinPanelBundle:User:changepass.html.twig', array(
    			'user' => $user,
    	));
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/user/updatepassword", name="update_password")
     * @Method("post")
     * @Template("EstokinPanelBundle:User:changepass.html.twig")
     */
    public function updatepasswordAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();

        $id = $request->get('id');

        $entity = $em->getRepository('EstokinPanelBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $oldpass = $request->get('oldpass');
        $newpass = $request->get('newpass');
        $newpassconf = $request->get('newpassconf');

        $factory = $this->get('security.encoder_factory');		
		$encoder = $factory->getEncoder($entity);  //echo 'pass: '.$oldpass."<br> salt: ".$entity->getSalt()."<br> ";
		$password = $encoder->encodePassword($oldpass, $entity->getSalt());  //echo "final pass: ".$password; die();
                
        if($password != $entity->getPassword()){
	        $arr = $this->get('session')->getFlashes();
            $arr['bad'][] = 'El password actual es incorrecto';
            $this->get('session')->setFlashes($arr);
            return array(
	            'user' => $entity,
	        );
        }
        else if($newpassconf != $newpass){
	        $arr = $this->get('session')->getFlashes();
            $arr['bad'][] = "Los nuevos passwords no coinciden";
            $this->get('session')->setFlashes($arr);
            return $this->redirect($this->generateUrl('change_password'));
        }
        else{
			$password = $encoder->encodePassword($newpass, $entity->getSalt());
			$entity->setPassword($password);	        
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('shop_profile'));
        }
        
/*
        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
*/
    }


    /**
     * Deletes a User entity.
     *
     * @Route("/user/{id}/delete", name="user_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('EstokinPanelBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * Show the login screen.
     *
     * @Route("/login", name="login")
     * @Route("/login_check", name="login_check")
     * @Route("/logout", name="logout")
     * 
     * @Template()
     */
    public function loginAction(){
    	
    	$request = $this->getRequest();
    	$session = $request->getSession();
    	
    	// get the login error if there is one
    	if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
    		$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
    	} else {
    		$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
    	}
    	
    	return $this->render('EstokinPanelBundle:User:login.html.twig', array(
    			// last username entered by the user
    			'last_username' => $session->get(SecurityContext::LAST_USERNAME),
    			'error'         => $error,
    	));
    }
    /**
     * Check the user/pass.
     *
     * @Route("/login_check", name="login_check")
     */
    /**
     * Check the user/pass.
     *
     * @Route("/logout", name="logout")
     */
}
