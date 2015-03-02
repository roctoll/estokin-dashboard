<?php
/**
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* Page-level DocBlock
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
 * PrestaShop Webservice Library
 * @package PrestaShopWebservice
 */

namespace Estokin\PanelBundle\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;
/**
 * @package PrestaShopWebservice
 */

class PrestaShopWebservice
{

	/** @var string Shop URL */
	protected $url;
	
	/** @var string Authentification key */
	protected $key;
	
	/** @var boolean is debug activated */
	protected $debug;
	
	/** @var string debug HTML */
	public $debugLog;

	public $id_shop;
	/** @var array compatible versions of PrestaShop Webservice */
	const psCompatibleVersionsMin = '1.5.0.0';
	const psCompatibleVersionsMax = '1.5.4.0';

	/**
	 * PrestaShopWebservice constructor. Throw an exception when CURL is not installed/activated
	 * <code>
	 * <?php
	 * require_once('./PrestaShopWebservice.php');
	 * try
	 * {
	 * 	$ws = new PrestaShopWebservice('http://mystore.com/', 'ZQ88PRJX5VWQHCWE4EE7SQ7HPNX00RAJ', false);
	 * 	// Now we have a webservice object to play with
	 * }
	 * catch (PrestaShopWebserviceException $ex)
	 * {
	 * 	echo 'Error : '.$ex->getMessage();
	 * }
	 * ?>
	 * </code>
	 * @param string $url Root URL for the shop
	 * @param string $key Authentification key
	 * @param mixed $debug Debug mode Activated (true) or deactivated (false)
	**/
	function __construct($url, $key, $debug = true) {
		if (!extension_loaded('curl'))
		  throw new PrestaShopWebserviceException('Please activate the PHP extension \'curl\' to allow use of PrestaShop webservice library');
		$this->url = $url;
		$this->key = $key;
		$this->debug = $debug;
		$this->debugLog = '';
		$this->id_shop = -1;
	}
	
	/**
	 * Take the status code and throw an exception if the server didn't return 200 or 201 code
	 * @param int $status_code Status code of an HTTP return
	 */
	protected function checkStatusCode($status_code)
	{
		$error_label = 'This call to PrestaShop Web Services failed and returned an HTTP status of %d. That means: %s.';
		switch($status_code)
		{
			case 200:
			case 201: return '';
			case 204: return sprintf($error_label, $status_code, 'No content');
			case 400: return sprintf($error_label, $status_code, 'Bad Request');
			case 401: return sprintf($error_label, $status_code, 'Unauthorized');
			case 404: return sprintf($error_label, $status_code, 'Not Found');
			case 405: return sprintf($error_label, $status_code, 'Method Not Allowed');
			case 500: return sprintf($error_label, $status_code, 'Internal Server Error');
			default: return 'This call to PrestaShop Web Services returned an unexpected HTTP status of:' . $status_code;
		}
	}
	/**
	 * Handles a CURL request to PrestaShop Webservice. Can throw exception.
	 * @param string $url Resource name
	 * @param mixed $curl_params CURL parameters (sent to curl_set_opt)
	 * @return array status_code, response
	 */
	protected function executeRequest($url, $curl_params = array())
	{
		$defaultParams = array(
			CURLOPT_HEADER => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLINFO_HEADER_OUT => TRUE,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $this->key.':',
			CURLOPT_HTTPHEADER => array( 'Expect:' )
		);
		if ($this->id_shop != -1)
			$url .= '&id_shop='.$this->id_shop;
		$session = curl_init($url);

		$curl_options = array();
		foreach ($defaultParams as $defkey => $defval)
		{
			if (isset($curl_params[$defkey]))
				$curl_options[$defkey] = $curl_params[$defkey];
			else
				$curl_options[$defkey] = $defaultParams[$defkey];
		}
		foreach ($curl_params as $defkey => $defval)
			if (!isset($curl_options[$defkey]))
				$curl_options[$defkey] = $curl_params[$defkey];

		curl_setopt_array($session, $curl_options);
		$response = curl_exec($session);

		$index = strpos($response, "\r\n\r\n");
		if ($index === false && $curl_params[CURLOPT_CUSTOMREQUEST] != 'HEAD')
			throw new PrestaShopWebserviceException('Bad HTTP response');
		
		$header = substr($response, 0, $index);
		$body = substr($response, $index + 4);
		
		$headerArrayTmp = explode("\n", $header);
		
		$headerArray = array();
		foreach ($headerArrayTmp as &$headerItem)
		{
			$tmp = explode(':', $headerItem);
			$tmp = array_map('trim', $tmp);
			if (count($tmp) == 2)
				$headerArray[$tmp[0]] = $tmp[1];
		}
		
		if (array_key_exists('PSWS-Version', $headerArray))
		{
			if (
				version_compare(PrestaShopWebservice::psCompatibleVersionsMin, $headerArray['PSWS-Version']) == 1 ||
				version_compare(PrestaShopWebservice::psCompatibleVersionsMax, $headerArray['PSWS-Version']) == -1
			)
			throw new PrestaShopWebserviceException('This library is not compatible with this version of PrestaShop. Please upgrade/downgrade this library');
		}
		
		$this->appendDebug($url);
		$this->appendDebug('HTTP REQUEST HEADER', curl_getinfo($session, CURLINFO_HEADER_OUT));
		$this->appendDebug('HTTP RESPONSE HEADER', $header);

		
		$status_code = curl_getinfo($session, CURLINFO_HTTP_CODE);
		if ($status_code === 0)
			throw new PrestaShopWebserviceException('CURL Error: '.curl_error($session));
		curl_close($session);

		if ($curl_params[CURLOPT_CUSTOMREQUEST] == 'PUT' || $curl_params[CURLOPT_CUSTOMREQUEST] == 'POST')
			$this->appendDebug('XML SENT', $curl_params[CURLOPT_POSTFIELDS]);
		if ($curl_params[CURLOPT_CUSTOMREQUEST] != 'DELETE' && $curl_params[CURLOPT_CUSTOMREQUEST] != 'HEAD')
			$this->appendDebug('RETURN HTTP BODY', $body);

		if ($this->debug)
			echo $this->debugLog;
		return array('status_code' => $status_code, 'response' => $body, 'header' => $header);
	}
	public function appendDebug($title, $content = '')
	{
		$i = 'content_'.substr(md5(rand()), 0, 5);
		if ($content == '')
			$this->debugLog .= '<h3>'.$title.'</h3>';
		else
			$this->debugLog .= '<div style="font-family: Arial; display:table;background:#CCC;font-size:8pt;padding:7px">
					<h6 style="font-size:9pt;margin:0" onClick="document.getElementById(\'content_'.$i.'\').style.display=\'block\';">'.$title.'</h6>
					<pre id="content_'.$i.'" style="display: none;">'.htmlentities($content).'</pre>
				</div>';
	}
	/**
	 * Load XML from string. Can throw exception
	 * @param string $response String from a CURL response
	 * @return SimpleXMLElement status_code, response
	 */
	protected function parseXML($request)
	{
		$errors = array();
		$status_code_error = self::checkStatusCode($request['status_code']);// check the response validity
		if ($status_code_error != '')
			$errors[] = $status_code_error;
			
		if (!$request['response'])
			throw new PrestaShopWebserviceException('HTTP response is empty');
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($request['response']);
		if (libxml_get_errors())
			$errors[] = 'HTTP XML response is not parsable : '.var_export(libxml_get_errors(), true);
		if (isset($xml->errors))
			foreach ($xml->errors->children() as $error)
				$errors[] = (string)$error->message; 

		if (count($errors)){ print_r($errors);
			throw new PrestaShopWebserviceException(implode('<br />', $errors));}
		return $xml;
	}
	
	/**
	 * Add (POST) a resource
	 * <p>Unique parameter must take : <br><br>
	 * 'resource' => Resource name<br>
	 * 'postXml' => Full XML string to add resource<br><br>
	 * Examples are given in the tutorial</p>
	 * @param array $options
	 * @return SimpleXMLElement status_code, response
	 */
	public function add($options)
	{
		$xml = '';

		if (isset($options['resource'], $options['postXml']) || isset($options['url'], $options['postXml']))
		{
			$url = (isset($options['resource']) ? $this->url.'/api/'.$options['resource'] : $options['url']);
			$xml = $options['postXml'];
		}
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		$request = self::executeRequest($url, array(CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => 'xml='.$xml));

		return self::parseXML($request);
	}

	/**
 	 * Retrieve (GET) a resource
	 * <p>Unique parameter must take : <br><br>
	 * 'url' => Full URL for a GET request of Webservice (ex: http://mystore.com/api/customers/1/)<br>
	 * OR<br>
	 * 'resource' => Resource name,<br>
	 * 'id' => ID of a resource you want to get<br><br>
	 * </p>
	 * <code>
	 * <?php
	 * require_once('./PrestaShopWebservice.php');
	 * try
	 * {
	 * $ws = new PrestaShopWebservice('http://mystore.com/', 'ZQ88PRJX5VWQHCWE4EE7SQ7HPNX00RAJ', false);
	 * $xml = $ws->get(array('resource' => 'orders', 'id' => 1));
	 *	// Here in $xml, a SimpleXMLElement object you can parse
	 * foreach ($xml->children()->children() as $attName => $attValue)
	 * 	echo $attName.' = '.$attValue.'<br />';
	 * }
	 * catch (PrestaShopWebserviceException $ex)
	 * {
	 * 	echo 'Error : '.$ex->getMessage();
	 * }
	 * ?>
	 * </code>
	 * @param array $options Array representing resource to get.
	 * @return SimpleXMLElement status_code, response
	 */
	public function get($options)
	{
		if (isset($options['url']))
			$url = $options['url'];
		elseif (isset($options['resource']))
		{
			$url = $this->url.'/api/'.$options['resource'];
			$url_params = array();
			if (isset($options['id']))
				$url .= '/'.$options['id'];
				
			$params = array('filter', 'display', 'sort', 'limit');
			foreach ($params as $p)
				foreach ($options as $k => $o)
					if (strpos($k, $p) !== false)
						$url_params[$k] = $options[$k];
			if (count($url_params) > 0)
				$url .= '?'.http_build_query($url_params);
		}
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		
		$request = self::executeRequest($url, array(CURLOPT_CUSTOMREQUEST => 'GET'));
		
		// check the response validity
		return self::parseXML($request);
	}

	/**
 	 * Head method (HEAD) a resource
	 *
	 * @param array $options Array representing resource for head request.
	 * @return SimpleXMLElement status_code, response
	 */
	public function head($options)
	{
		if (isset($options['url']))
			$url = $options['url'];
		elseif (isset($options['resource']))
		{
			$url = $this->url.'/api/'.$options['resource'];
			$url_params = array();
			if (isset($options['id']))
				$url .= '/'.$options['id'];
				
			$params = array('filter', 'display', 'sort', 'limit');
			foreach ($params as $p)
				foreach ($options as $k => $o)
					if (strpos($k, $p) !== false)
						$url_params[$k] = $options[$k];
			if (count($url_params) > 0)
				$url .= '?'.http_build_query($url_params);
		}
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		$request = self::executeRequest($url, array(CURLOPT_CUSTOMREQUEST => 'HEAD', CURLOPT_NOBODY => true));
		$status_code_error = self::checkStatusCode($request['status_code']);// check the response validity
		if ($status_code_error != '')
			throw new PrestaShopWebserviceException($status_code_error);
		return $request['header'];
	}
	/**
	 * Edit (PUT) a resource
	 * <p>Unique parameter must take : <br><br>
	 * 'resource' => Resource name ,<br>
	 * 'id' => ID of a resource you want to edit,<br>
	 * 'putXml' => Modified XML string of a resource<br><br>
	 * Examples are given in the tutorial</p>
	 * @param array $options Array representing resource to edit.
	 */
	public function edit($options)
	{
		$xml = '';
		if (isset($options['url']))
			$url = $options['url'];
		elseif ((isset($options['resource'], $options['id']) || isset($options['url'])) && $options['putXml'])
		{
			$url = (isset($options['url']) ? $options['url'] : $this->url.'/api/'.$options['resource'].'/'.$options['id']); 
			$xml = $options['putXml']; 
		}
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		
		$request = self::executeRequest($url,  array(CURLOPT_CUSTOMREQUEST => 'PUT', CURLOPT_POSTFIELDS => $xml)); //print_r($request);die();
		return self::parseXML($request);
	}

	/**
	 * Delete (DELETE) a resource.
	 * Unique parameter must take : <br><br>
	 * 'resource' => Resource name<br>
	 * 'id' => ID or array which contains IDs of a resource(s) you want to delete<br><br>
	 * <code>
	 * <?php
	 * require_once('./PrestaShopWebservice.php');
	 * try
	 * {
	 * $ws = new PrestaShopWebservice('http://mystore.com/', 'ZQ88PRJX5VWQHCWE4EE7SQ7HPNX00RAJ', false);
	 * $xml = $ws->delete(array('resource' => 'orders', 'id' => 1));
	 *	// Following code will not be executed if an exception is thrown.
	 * 	echo 'Successfully deleted.';
	 * }
	 * catch (PrestaShopWebserviceException $ex)
	 * {
	 * 	echo 'Error : '.$ex->getMessage();
	 * }
	 * ?>
	 * </code>
	 * @param array $options Array representing resource to delete.
	 */
	public function delete($options)
	{
		if (isset($options['url']))
			$url = $options['url'];
		elseif (isset($options['resource']) && isset($options['id']))
			if (is_array($options['id']))
				$url = $this->url.'/api/'.$options['resource'].'/?id=['.implode(',', $options['id']).']';
			else
				$url = $this->url.'/api/'.$options['resource'].'/'.$options['id'];
		$request = self::executeRequest($url, array(CURLOPT_CUSTOMREQUEST => 'DELETE'));
		$status_code_error = self::checkStatusCode($request['status_code']);// check the response validity
		if ($status_code_error != '')
			throw new PrestaShopWebserviceException($status_code_error);
		return true;
	}
	
	public function setShop($id_shop)
	{
		$this->id_shop = $id_shop;
	}

	public function addImage($method, $image_path, $type, $id = '')
	{
		$url = $this->url.'/api/images/'.$type.'/'.$id;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($method == 'POST')
			curl_setopt($ch, CURLOPT_POST, true);
		if ($method == 'PUT')
			curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->key.':');
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => '@'.$image_path));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}

/**
 * @package PrestaShopWebservice
 */
class PrestaShopWebserviceException extends httpException { }

