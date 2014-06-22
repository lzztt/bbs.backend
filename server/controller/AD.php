<?php

namespace site\controller;

use site\Controller;
use lzx\html\HTMLElement;
use lzx\html\Template;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ad
 *
 * @author ikki
 */
class AD extends Controller
{

   protected function _default()
   {
      //$this->request->pageNotFound();
      
      $this->cache->setStatus(FALSE);

      $func = $this->args[1] ? $this->args[1] : 'yp';
      if (method_exists($this, $func))
      {
         $this->html->var['content'] = $this->$func();
      }
   }

   public function yp()
   {
       $form_yp_sp = <<<'YP_SP'
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="FKY5DLSE3T7SY">
<table>
<tr><td><input type="hidden" name="on0" value="Ad Months">Ad Months</td></tr><tr><td><select name="os0">
	<option value="3 months">3 months $120.00 USD</option>
	<option value="6 months">6 months $195.00 USD</option>
	<option value="12 months">12 months $306.00 USD</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
YP_SP;
       
      $form_yp = <<<'YP'
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="PEP9ASKEHJV7Q">
<table>
<tr><td><input type="hidden" name="on0" value="Time">Time</td></tr><tr><td><select name="os0">
	<option value="3 months">3 months $240.00 USD</option>
	<option value="6 months">6 months $420.00 USD</option>
	<option value="12 months">12 months $720.00 USD</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
YP;


      $form_banner = <<<'HEAD'
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="N2NJXZZRT69RS">
<table>
<tr><td><input type="hidden" name="on0" value="Time">Time</td></tr><tr><td><select name="os0">
	<option value="3 months">3 months $800.00 USD</option>
	<option value="6 months">6 months $1,400.00 USD</option>
	<option value="12 months">12 months $2,400.00 USD</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
HEAD;

      $content = new HTMLElement('div', NULL);
/*      
      $form = new HTMLElement('div', NULL);
      $form->setDataByIndex(NULL, new HTMLElement('h3', 'Yellow Page Web Advertisement (SH Home Remodeling)'));
      $form->setDataByIndex(NULL, $form_yp_sp);
      $content->setDataByIndex(NULL, $form);
 */     
      $form = new HTMLElement('div', NULL);
      $form->setDataByIndex(NULL, new HTMLElement('h3', 'Yellow Page Web Advertisement'));
      $form->setDataByIndex(NULL, $form_yp);
      $content->setDataByIndex(NULL, $form);

      $form = new HTMLElement('div', NULL);
      $form->setDataByIndex(NULL, new HTMLElement('h3', 'Page Header Web Advertisement'));
      $form->setDataByIndex(NULL, $form_banner);
      $content->setDataByIndex(NULL, $form);

      return $content;
   }

   public function header()
   {
      return 'header';
   }

   public function cancel()
   {
      return 'cancel';
   }

   public function success()
   {
      return 'success';
   }

}

//__END_OF_FILE__
