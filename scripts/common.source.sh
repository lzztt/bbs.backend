# this file need to be source by other bash scripts

function error_exit
{
   ## print error message
   echo "Error: $1"
   exit 1;
} 1>&2
