<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\EventDispatcher;

namespace lzx\event;

/**
 * Event is the base class for classes containing event data.
 *
 * This class contains no event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * You can call the method stopPropagation() to abort the execution of
 * further listeners in your event listener.
 *
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Event
{

   /**
    * @var Boolean Whether no further event listeners should be triggered
    */
   // public read-only properties, initialized when create object
   protected $name;
   protected $source;
   // public value type restrictions, use getter and setter to read/write
   protected $args;
   protected $return;
   protected $propagation = TRUE;

// we don't handle exception here, just throw it.

   public function __construct($name, $source, array $args = array())
   {
      if (!is_string($name))
      {
         throw new \InvalidArgumentException('invalid event name type (requires string)');
      }
      if (!is_object($source))
      {
         throw new \InvalidArgumentException('invalid event source type (requires object)');
      }

      $this->name = $name;
      $this->source = $source;
      $this->args = $args;
   }

   final public function getName()
   {
      return $this->name;
   }

   // @return \stdClass
   final public function getSource()
   {
      return $this->source;
   }

   final public function getArgs()
   {
      return array_values($this->args);
   }

   final public function getReturn()
   {
      return $this->return;
   }

   /**
    * Stops the propagation of the event to further event listeners.
    *
    * If multiple event listeners are connected to the same event, no
    * further event listener will be triggered once any trigger calls
    * stopPropagation().
    *
    * @api
    */
   public function isPropagationStopped()
   {
      return ($this->propagation === FALSE);
   }

   /**
    * Stops the propagation of the event to further event listeners.
    *
    * If multiple event listeners are connected to the same event, no
    * further event listener will be triggered once any trigger calls
    * stopPropagation().
    *
    * @api
    */
   public function stopPropagation()
   {
      $this->propagation = FALSE;
   }

}
