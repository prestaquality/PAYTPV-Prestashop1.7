<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author     Jose Ramon Garcia <jrgarcia@paytpv.com>
*  @copyright  2015 PAYTPV ON LINE S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

/**
 * @since 1.5.0
 */
class PaytpvAccountModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	public function init()
	{

		parent::init();
	}


	public function getBreadcrumbLinks(){
	    $breadcrumb = parent::getBreadcrumbLinks();
 
	    $breadcrumb['links'][] = [
	        'title' => $this->getTranslator()->trans('Your account', array(), 'Shop.Theme.Customeraccount'),
            'url' => $this->context->link->getPageLink('my-account', true),
	     ];
	 
	    
	 
	     return $breadcrumb;
	 }

	public function initContent()
	{
		parent::initContent();
		
		$error = "";

		$this->context->controller->addJqueryPlugin('fancybox');

		if (!Context::getContext()->customer->isLogged())
			Tools::redirect('index.php?controller=authentication&redirect=module&module=paytpv&action=account');

		if (Context::getContext()->customer->id)
		{
			$paytpv = $this->module;


			$arrTerminal = Paytpv_Terminal::getTerminalByCurrency($this->context->currency->iso_code);
			$idterminal = $arrTerminal["idterminal"];
			$idterminal_ns = $arrTerminal["idterminal_ns"];
			$pass = $arrTerminal["password"];
			$pass_ns = $arrTerminal["password_ns"];
			$jetid = $arrTerminal["jetid"];
			$jetid_ns = $arrTerminal["jetid_ns"];

			// PAGO SEGURO
			if ($idterminal>0)
				$secure_pay = $paytpv->isSecureTransaction($idterminal,0,0)?1:0;
			else
				$secure_pay = $paytpv->isSecureTransaction($idterminal_ns,0,0)?1:0;

			// Miramos a ver por que terminal enviamos la operacion
			if ($secure_pay){
				$idterminal_sel = $idterminal;
				$pass_sel = $pass;
				$jetid_sel = $jetid;
			}else{
				$idterminal_sel = $idterminal_ns;
				$pass_sel = $pass_ns;
				$jetid_sel = $jetid_ns;
			}
		

			// BANKSTORE JET
		    $token = isset($_POST["paytpvToken"])?$_POST["paytpvToken"]:"";


		    if ($token && strlen($token) == 64){
		    	include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');

		    	$client = new WS_Client(
					array(
						'clientcode' => $paytpv->clientcode,
						'term' => $idterminal_sel,
						'pass' => $pass_sel,
						'jetid' => $jetid_sel
					)
				);

				$addUserResponse = $client->add_user_token($token);
				if ( ( int ) $addUserResponse[ 'DS_ERROR_ID' ] > 0 ) {
					$error = $paytpv->l('Cannot operate with given credit card');

				}else{
					$data["IDUSER"] = $addUserResponse["DS_IDUSER"];
					$data["TOKEN_USER"] = $addUserResponse["DS_TOKEN_USER"];
					$result = $client->info_user( $data["IDUSER"],$data["TOKEN_USER"]);
					$paytpv->saveCard((int)$this->context->customer->id,$data["IDUSER"],$data["TOKEN_USER"],$result['DS_MERCHANT_PAN'],$result['DS_CARD_BRAND']);
			
				}
			}
			
			$saved_card = Paytpv_Customer::get_Cards_Customer((int)$this->context->customer->id);

			$language_data = explode("-",$this->context->language->language_code);
			$language = $language_data[0];

			$suscriptions = Paytpv_Suscription::get_Suscriptions_Customer($language,(int)$this->context->customer->id);

			$order = Context::getContext()->customer->id;
			$operation = 107;
			$ssl = Configuration::get('PS_SSL_ENABLED');
			$paytpv_integration = intval(Configuration::get('PAYTPV_INTEGRATION'));

			
			$URLOK=$URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'account',array(),$ssl);  

			$language_data = explode("-",$this->context->language->language_code);
			$language = $language_data[0];

			// Cálculo Firma
			$signature = md5($paytpv->clientcode.$idterminal_sel.$operation.$order.md5($pass_sel));
			$fields = array
			(
				'MERCHANT_MERCHANTCODE' => $paytpv->clientcode,
				'MERCHANT_TERMINAL' => $idterminal_sel,
				'OPERATION' => $operation,
				'LANGUAGE' => $language,
				'MERCHANT_MERCHANTSIGNATURE' => $signature,
				'MERCHANT_ORDER' => $order,
				'URLOK' => $URLOK,
			    'URLKO' => $URLKO,
			    '3DSECURE' => $secure_pay
			);

			$query = http_build_query($fields);

			$vhash = hash('sha512', md5($query.md5($pass_sel))); 

			$url_paytpv = $paytpv->url_paytpv . "?".$query . "&VHASH=".$vhash;
			
			$paytpv_path = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$paytpv->name.'/';
			
			$this->context->controller->addCSS( $paytpv_path . 'css/account.css' , 'all' );
			$this->context->controller->addCSS( $paytpv_path . 'css/fullscreen.css' , 'all' );
		 	$this->context->controller->addJS( $paytpv_path . 'js/paytpv_account.js');

			$this->context->smarty->assign('url_paytpv',$url_paytpv);
			$this->context->smarty->assign('saved_card',$saved_card);
			$this->context->smarty->assign('suscriptions',$suscriptions);
			$this->context->smarty->assign('base_dir', __PS_BASE_URI__);

			$this->context->smarty->assign('url_removecard',Context::getContext()->link->getModuleLink('paytpv', 'actions', array("process"=>"removeCard"), true));
			$this->context->smarty->assign('url_savedesc',Context::getContext()->link->getModuleLink('paytpv', 'actions', array("process"=>"saveDescriptionCard"), true));
			$this->context->smarty->assign('url_cancelsuscription',Context::getContext()->link->getModuleLink('paytpv', 'actions',array("process"=>"cancelSuscription"), true));
			
			$this->context->smarty->assign('newpage_payment', $paytpv->newpage_payment);

			$this->context->smarty->assign('paytpv_integration',$paytpv_integration);

			$this->context->smarty->assign('jet_id',$jetid);

			$language_data = explode("-",$this->context->language->language_code);
			$language = $language_data[0];

			$this->context->smarty->assign('jet_lang',$language);

			$this->context->smarty->assign('paytpv_jetid_url',Context::getContext()->link->getModuleLink('paytpv', 'account',array(),$ssl));

			$this->context->smarty->assign('error',$error);

			// Bankstore JET
			if ($paytpv_integration==1){

				$this->context->smarty->assign('js_code', $paytpv->js_minimized_jet());

				$this->context->smarty->assign('this_path', $this->module->getPath());

			}

			$this->context->smarty->assign('status_canceled',$paytpv->l('CANCELLED'));
			
			$this->context->smarty->assign(array(
				'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
				'base_dir' =>  __PS_BASE_URI__
			));

			$this->setTemplate('module:paytpv/views/templates/front/paytpv-account.tpl');
			
		}
	}
}