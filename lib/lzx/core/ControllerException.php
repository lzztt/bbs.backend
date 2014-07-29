<?php

namespace lzx\core;

/**
 * Description of ControllerException
 *
 * @author ikki
 */
class ControllerException extends \Exception
{

   const PAGE_ERROR = 0;
   const PAGE_NOTFOUND = 1;
   const PAGE_FORBIDDEN = 2;
   const PAGE_REDIRECT = 3;

}

//__END_OF_FILE__
