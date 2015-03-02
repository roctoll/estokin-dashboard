<?php
namespace Estokin\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Basic controller.
 *
 * @Route("")
 */
class BasicController extends Controller
{
	/**
	 * (Home)
	 *
	 * @Route("/public", name="public")
	 * @Template()
	 */
	public function publicAction()
	{
		return $this->render('EstokinPanelBundle:Static:public.html.twig');
	}
	/**
	 * (Condiciones de uso)
	 *
	 * @Route("/conditions", name="conditions")
	 * @Template()
	 */
	public function conditionsAction()
	{
		return $this->render('EstokinPanelBundle:Static:conditions.html.twig');
	}
	
	/**
	 * (Info request)
	 *
	 * @Route("/public/inforeq", name="info_req")
	 * @Method("post")
	 */
	public function inforeqAction()
	{
		$request = $this->getRequest();
		$message =	"Tienda: ".$request->get('_name')."\n".
					"Mail: ".$request->get('_mail')."\n".
					"Población: ".$request->get('_loaction')."\n".
					"Sector: ".$request->get('_sector');	  	
		
		mail( "roc@estokin.com", "Solicitud de registro Estokin.com", $message, "From: web@estokin.com" );
		$this->get('session')->setFlash('ok', 'yes');

		return $this->redirect($this->generateUrl('public'));
	}
	
	/**
	 * (Contact)
	 *
	 * @Route("/public/contact", name="contact")
	 * @Template()
	 */
	public function contactAction()
	{
		return $this->render('EstokinPanelBundle:Static:contact.html.twig');
	}
	/**
	 * (Help) Static helpful info.
	 *
	 * @Route("/help", name="help")
	 * @Template()
	 */
	public function helpAction()
	{
		return $this->render('EstokinPanelBundle:Static:help.html.twig');
	}
	/**
	 * (Faq) Static helpful info.
	 *
	 * @Route("/faq", name="faq")
	 * @Template()
	 */
	public function faqAction()
	{
		return $this->render('EstokinPanelBundle:Static:faq.html.twig');
	}
}