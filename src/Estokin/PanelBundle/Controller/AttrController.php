<?php

namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Estokin\PanelBundle\Entity\Attr;
use Estokin\PanelBundle\Form\AttrType;

/**
 * Attr controller.
 *
 * @Route("/admin/attr")
 */
class AttrController extends Controller
{
    /**
     * Lists all Attr entities.
     *
     * @Route("/", name="attr")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('EstokinPanelBundle:Attr')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Attr entity.
     *
     * @Route("/{id}/show", name="attr_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Attr')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Attr entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Attr entity.
     *
     * @Route("/new", name="attr_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Attr();
        $form   = $this->createForm(new AttrType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Attr entity.
     *
     * @Route("/create", name="attr_create")
     * @Method("post")
     * @Template("EstokinPanelBundle:Attr:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Attr();
        $request = $this->getRequest();
        $form    = $this->createForm(new AttrType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('attr_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Attr entity.
     *
     * @Route("/{id}/edit", name="attr_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Attr')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Attr entity.');
        }

        $editForm = $this->createForm(new AttrType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Attr entity.
     *
     * @Route("/{id}/update", name="attr_update")
     * @Method("post")
     * @Template("EstokinPanelBundle:Attr:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EstokinPanelBundle:Attr')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Attr entity.');
        }

        $editForm   = $this->createForm(new AttrType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('attr_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Attr entity.
     *
     * @Route("/{id}/delete", name="attr_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('EstokinPanelBundle:Attr')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Attr entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('attr'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
