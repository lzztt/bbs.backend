<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;

/**
 * Description of schools
 *
 * @author ikki
 */
abstract class Schools extends Controller
{

   public function run()
   {
      $this->html->var['content'] = $this->getSchoolList();
   }

   //put your code here
   public function getSchoolList()
   {
      $doc = new \DOMDocument();

      // We need to validate our document before refering to the id
      $doc->validateOnParse = TRUE;
      //@$doc->loadHTML($this->curlGetData('http://www.har.com/school/dispExempSchools.cfm'));
      @$doc->loadHTML(\file_get_contents('/home/ikki/dispExempSchools.cfm'));

      $schoolElements = [
         'Elementary' => $doc->getElementById('elemSchool'),
         'Middle' => $doc->getElementById('MiddleSchool'),
         'High' => $doc->getElementById('HighSchool'),
      ];

      $schools = [];

      foreach ($schoolElements as $key => $xml)
      {
         $table = $xml->getElementsByTagName('table')->item(0);
         $i = 0;

         $school = [];
         foreach ($table->getElementsByTagName('tr') as $tr)
         {
            //skip 2 header rows
            if ($i < 2)
            {
               $i++;
               continue;
            }

            $j = 0;
            foreach ($tr->getElementsByTagName('td') as $td)
            {
               $j++;
               if ($j == 3)
               {
                  $school['name'] = $td->textContent;
               }
               if ($j == 4)
               {
                  $school['district'] = $td->textContent;
               }
               if ($j == 6)
               {
                  $school['city'] = $td->textContent;
               }
               if ($j == 7)
               {
                  $school['phone'] = $td->textContent;
               }
               if ($j == 8)
               {
                  $url = 'http://www.har.com/school/' . str_replace(' ', '%20', $td->getElementsByTagName('a')->item(0)->getAttribute('href'));
                  $info = new \DOMDocument();

                  // We need to validate our document before refering to the id
                  $info->validateOnParse = TRUE;
                  @$info->loadHtml($this->curlGetData($url));
                  $content = $info->getElementById('body_content');
                  $table = $content->getElementsByTagName('table')->item(1);
               }
            }
         }
         $schools[$key][] = $school;
      }
   }

   public function curlGetData($url)
   {
      $c = \curl_init($url);
      \curl_setopt_array($c, [
         CURLOPT_RETURNTRANSFER => TRUE,
         CURLOPT_CONNECTTIMEOUT => 20,
         CURLOPT_TIMEOUT => 30
      ]);
      $data = \curl_exec($c);
      \curl_close($c);

      return $data; // will return FALSE on failure
   }

}

?>