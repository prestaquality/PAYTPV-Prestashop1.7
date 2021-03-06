{*
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
*}
{extends file='page.tpl'}
{capture name=path}{l s='Payment not completed' mod='paytpv'}{/capture}


{block name='page_content'}
<h2>{l s='Payment not completed' mod='paytpv'}</h2>


	{if ($password_fail)}
	<img src="{$this_path}views/img/icon-cancel.png"/> &nbsp;&nbsp; {l s='Incorrect Password'  mod='paytpv'}  
	
	{else if ($error_msg)}
	<img src="{$this_path}views/img/icon-cancel.png"/> &nbsp;&nbsp; {$error_msg}
	{else}
	<img src="{$this_path}views/img/icon-cancel.png"/>&nbsp;&nbsp;   
	{l s='We are sorry. Your payment has not been completed. You can try again or choose another payment method. Remember that you can use cards adhered to the Visa secure payment system called "Verified by Visa", or MasterCard secure payment system, called "MasterCard SecureCode"'  mod='paytpv'}
	{/if}

<ul class="footer_links">    
	<li>    	
		<a href="{$link->getPageLink('my-account')}" title="{l s='Go to your account'  mod='paytpv'}">    		
			<img src="{$this_path}views/img/nav-user.gif" alt="{l s='Go to your account' mod='paytpv'}" class="icon" />&nbsp;{l s='Go to your account'  mod='paytpv'}    	
		</a>
	</li>
	<li>&nbsp;&nbsp;</li>    
	<li>    	
		<a href="{$link->getPageLink('order',false, NULL,'step=3')}" title="{l s='Select payment method'  mod='paytpv'}">    		
			<img src="{$this_path}views/img/cart.gif" alt="{l s='Select payment method' mod='paytpv'}" class="icon" />&nbsp;{l s='Select payment method'  mod='paytpv'}    	
	    </a>    
	</li>    
	<li>&nbsp;&nbsp;</li>    
	<li>    	
		<a href="{$base_dir}">    		
			<img src="{$this_path}views/img/home.gif" alt="{l s='Home' mod='paytpv'}" class="icon" />&nbsp;{l s='Home'  mod='paytpv'}    	
		</a>    
	</li>
</ul>


{/block}