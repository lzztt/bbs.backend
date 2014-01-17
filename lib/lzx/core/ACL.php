<?php
/**
 * Description of ACL
 *
 * @author ikki
 */
class ACL
{
   const MEMBER_ONLY = 1;
   const SELF_MODERATOR = 2; //self and moderator
   const SELF_ONLY = 3;
   const MODERATOR_ONLY = 4;
   const ROOT_ONLY = 5; // super user

   public static $permission = [];
   public static $action = [];

   /*
    * @param int $id uid for SELF check and
    */
   public static function validate($permission = self::MEMBER_ONLY, $id = NULL)
   {
      if (Request::$uid == 0) // guest always fail
      {
         return FALSE;
      }

      if ($permission == self::MEMBER_ONLY) // member passed member-only validation
      {
         return TRUE;
      }

      if (Request::$uid == ROOT_UID) // root pass all validation
      {
         return TRUE;
      }

      if ($permission == self::SELF_ONLY)
      {
         return (Request::$uid == $id);
      }

      //TODO: MODERATOR_ONLY need callback functions and database

      return FALSE;
   }
}

//__END_OF_FILE__
