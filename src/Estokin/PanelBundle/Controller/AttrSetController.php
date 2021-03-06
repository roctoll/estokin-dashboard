<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\AttrSet;
use Estokin\PanelBundle\Form\AttrSetType;

/**
 * AttrSet controller.
 *
 * @Route("/admin/attrset")
 */
class AttrSetController extends Controller
{
    /**
     * Lists all AttrSet entities.
     *
     * @Route("/", name="attrset")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('EstokinPanelBundle:AttrSet')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a AttrSet entity.
     *
     * @Route("/{id}/show", name="attrset_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:AttrSet')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AttrSet entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new AttrSet entity.
     *
     * @Route("/new", name="attrset_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new AttrSet();
        $form   = $this->createForm(new AttrSetType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new AttrSet entity.
     *
     * @Route("/create", name="attrset_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:AttrSet:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new AttrSet();
        $request = $this->getRequest();
        $form    = $this->createForm(new AttrSetType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('attrset_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing AttrSet entity.
     *
     * @Route("/{id}/edit", name="attrset_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:AttrSet')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AttrSet entity.');
        }

        $editForm = $this->createForm(new AttrSetType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing AttrSet entity.
     *
     * @Route("/{id}/update", name="attrset_update")
     * @Method("post")
     * @Template("EstokinPanelBundle:AttrSet:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:AttrSet')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AttrSet entity.');
        }

        $editForm   = $this->createForm(new AttrSetType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('attrset_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a AttrSet entity.
     *
     * @Route("/{id}/delete", name="attrset_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('EstokinPanelBundle:AttrSet')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AttrSet entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('attrset'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
