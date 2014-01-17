<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx;
/**
 * Description of Hook
 *
 * @author ikki
 */

use lzx\Event\Dispatcher;

abstract class Controller
{
   protected $_dispatcher;

   //put your code here
   public function __construct(Dispatcher $dispatcher)
   {

   }

   protected function getDispatcher()
   {
      return $this->_dispatcher;
   }

   // function example
   protected function test(stdClass $obj, array $array, callable $function, $function_name_Callable, $id_Int = 0, $price_Float = 0, $name_Str = '', $true_Bool = FALSE)
   {
      $eventName = 'pre_' . get_class($this) . '::' . __FUNCTION__;
      list() = $this->_hook($eventName, func_get_args());

      if(array_key_exists(__FUNCTION__, $this->bypass))
      {
         $return = $this->bypass[__FUNCTION__];
         unset($this->bypass[__FUNCTION__]);
      }
      else
      {
         // main function here;
      }

      // validate $args types

      $eventName = 'post_' . get_class($this) . '::' . __FUNCTION__;
      $return = $this->_hook($eventName, [$return]);

      // validate $return types

      return $return;
   }

   final protected function _hook($eventName, $args)
   {
      if($this->_dispatcher instanceof Dispatcher)
      {
         throw new \Exception('invalid event dispatcher');
      }

      $event = new Event\Event($eventName, $this, $args);
      $this->dispatcher->dispatch($event);

      return $event->getReturn();
   }

}

//__END_OF_FILE__
