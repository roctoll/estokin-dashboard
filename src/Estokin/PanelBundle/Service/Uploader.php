<?php


namespace Estokin\PanelBundle\Service;

require_once(dirname(__FILE__).'/ps/PSWebServiceLibrary.php');


class Uploader
{
	private $pspath;
	private $pskey;
	
	public function __construct($pspath, $pskey)
	{
		$this->pspath = $pspath;
		$this->pskey = $pskey;
	}

	public function PS_upload_product($entity){

		try
		{
			$webService = new PrestaShopWebservice($this->pspath, $this->pskey, true);
			$xml = $webService->get(array('url' => $this->pspath.'/api/products?schema=blank'));
			// Here we get the elements from children of customer markup which is children of prestashop root markup
			$resources = $xml->children()->children();
			//XML fails if don't delete this nodes
			//unset($resources->position_in_category);
			//unset($resources->manufacturer_name);
		 
		}
		catch (PrestaShopWebserviceException $e)
		{
			// Here we are dealing with errors			
			$trace = $e->getTrace();
			if ($trace[0]['args'][0] == 404) echo 'Bad ID';
			else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
			echo 'Other error: <br />' . $e->getMessage();
			return false;
		}
	
		// Here we have XML before update, lets update XML with new values
		//$resources->quantity = 0;
		$resources->price = $entity->getPrice();
		//$resources->out_of_stock = '0';
		$resources->name = $entity->getName();
		$resources->link_rewrite = str_replace(" ","-",$entity->getName());
	
		// Meta
		//$resources->meta_keywords = 'metakeywords';
		//$resources->meta_title = $entity->getName();
		$resources->description = $entity->getDescription();
		$resources->id_category_default = $entity->getCategory()->getPSId();
		// References
		//$resources->reference = $entity->getCode();

		//$resources->id_manufacturer = $entity->getBrand()->getId();
		//$resources->supplier_reference = $entity->getShop()->getName();
	
		// Inventaire
		$resources->active = '1';
		$resources->available_for_order = '1';
		$resources->condition = 'new';
		$resources->show_price = '1';
		$resources->minimal_quantity = 1;

		//unset($resources->quantity);
		unset($resources->manufacturer_name);
		unset($resources->position_in_category);	
		unset($resources->associations->product_features);
		unset($resources->associations->combinations->combinations);
		
			
		//unset($resources->associations->categories->category);
		$resources->associations->categories->addChild('category')->addChild('id', $entity->getCategory()->getPSId());
		// And call the web service
		try
		{
			$opt = array('resource' => 'products');
			$opt['postXml'] = $xml->asXML(); 
			$xml = $webService->add($opt);
			// if WebService don't throw an exception the action worked well and we don't show the following message
			// Here we get the elements from children of customer markup which is children of prestashop root markup
			return $xml->children()->children()->id;
			//return true;
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
	
	/**
	 *
	 * Upload the Item entity to Prestashop
	 * @param Item $entity
	 * @return int
	 */
	public function PS_upload_item($entity){
		 
		try
		{
			$webService = new PrestaShopWebservice($this->pspath, $this->pskey, false);
			$opt = array('resource' =>'combinations');
			$xml = $webService->get(array('url' => $this->pspath.'/api/combinations?schema=blank'));
	
			// Here we get the elements from children of customer markup which is children of prestashop root markup
			$resources = $xml->children()->children();
	
			 
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
	
		// Here we have XML before update, lets update XML with new values
	
		$resources->id_product = $entity->getProduct()->getPSId();
		//$resources->quantity = $entity->getStock();
		$resources->minimal_quantity = 1;
		$resources->default_on = 1;
		unset($resources->associations->images);
		
		// References
		//$resources->reference = $entity->getReference();
		//$resources->supplier_reference = $entity->getShop()->getName();
	
		if($entity->getAttr()){
			// Remove the first child of "product_option_values" cause is null and create problems
			unset($resources->associations->product_option_values->product_option_value);
			foreach($entity->getAttr() as $attr){ 
				$resources->associations->product_option_values->addChild('product_option_value')->addChild('id', $attr->getPSid());
			}
		}
		// And call the web service
		try
		{
			$opt = array('resource' => 'combinations');
			$opt['postXml'] = $xml->asXML();
			$xml = $webService->add($opt);
			// if WebService don't throw an exception the action worked well and we don't show the following message
			 	
	
			// Here we get the elements from children of customer markup which is children of prestashop root markup
			$ps_id = (int)$xml->children()->children()->id; 

			if($this->PS_setStock_item($ps_id,$entity->getStock())) return $ps_id;
			else return false;

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
	
	/**
	 *
	 * Remove the Item entity of Prestashop
	 * @param int $PSid
	 * @return int
	 */
	public function PS_remove_item($PSid){
	
		try
		{
			$webService = new PrestaShopWebservice($this->pspath, $this->pskey, false);
			$opt = array('resource' =>'combinations');
			$opt['id'] = $PSid;
			$webService->delete($opt);
			return true;
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
	
	public function PS_setStock_item($id, $stock, $ses){ 
		try
		{
			$webService = new PrestaShopWebservice($this->pspath, $this->pskey, false);
						
			$opt = array(
				'resource'						=>	'stock_availables',
				'filter[id_product_attribute]'	=>	$id	
			);
			$xml = $webService->get($opt);
			// Here we get the elements from children of customer markup which is children of prestashop root markup
			$resources = $xml->children()->children();
			$id_st = (int)$resources->stock_available['id'];		
			$opt = array('resource'	=>	'stock_availables'); 
			
			$opt['id'] = $id_st;
			$xml = $webService->get($opt); //print_r($xml->asXML()); 
						
			$resources = $xml->children()->children(); 
		}
		catch (PrestaShopWebserviceException $e)
		{
			// Here we are dealing with errors
			
			$arr = $ses->getFlashes();
			
			$trace = $e->getTrace(); return $trace[0]['args'];
/* 			if ($trace[0]['args'][0] == 404) echo 'Bad ID'; */
			if ($trace[0]['args'][0] == 404) $arr['bad'][] = 'Bad ID';
/* 			else if ($trace[0]['args'][0] == 401) echo 'Bad auth key'; */
			else if ($trace[0]['args'][0] == 401) $arr['bad'][] = 'Bad auth key';
/* 			else echo 'Other error'; */
			else $arr['bad'][] = $trace[0]['args'][1];
			
			$ses->setFlashes($arr);
			
			return false;
		}
	
		// Here we have XML before update, lets update XML with new values
	
		$resources->quantity = $stock;
		//$resources->price = $resources->my_price; //patch -> default price in GET is not the same as PUT
	
		// And call the web service
		try
		{
			$opt = array('resource' => 'stock_availables');
			$opt['putXml'] = $xml->asXML();
			$opt['id'] = $id_st;
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
}
