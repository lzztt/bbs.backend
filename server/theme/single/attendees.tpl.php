<div style="background-color: #76a3f0;">
   <?php foreach ( $groups as $i => $attendees ): ?>
      <div><h3><?php print $i ? '男生' : '女生'  ?></h3>
         <?php foreach ( $attendees as $i => $a ): ?>
            <div class="even_odd">
               <div class="atd_name"><span class="atd_no"><?php print $i + 1; ?></span><?php print $a[ 'name' ]; ?></div>
               <div class="atd_email"><?php print $a[ 'email' ]; ?></div>
               <?php if ( $a[ 'info' ] ): ?>
                  <div class="atd_info"><?php print \nl2br( $a[ 'info' ] ); ?></div>
               <?php endif; ?>
               <?php if ( $a[ 'questions' ] ): ?>
                  <div class="atd_questions"><?php print \implode( '<br />', $a[ 'questions' ] ); ?></div>
               <?php endif; ?>
            </div>
         <?php endforeach; ?>
      </div>
   <?php endforeach; ?>
</div>

<style type="text/css">
   h3
   {
      margin: 0;
      padding: 1em;
      text-align: center;
   }
   div.even_odd:nth-child(even)
   {
      background-color: #EAF2D3;
   }
   div.even_odd:nth-child(odd)
   {
      background-color: pink;
   }

   div.atd_name
   {
      display: inline-block;
      width: 150px;
      font-weight: bolder;
      padding: 5px 0;
   }

   span.atd_no
   {
      display: inline-block;
      width: 30px;
      color: blue;
      text-align: center;
   }

   div.atd_email
   {
      display: inline-block;
   }

   div.atd_info, div.atd_questions
   {
      margin-left: 30px;
      padding: 5px 0;
   }

   div.atd_info
   {
      color: #5A005A;
   }
</style>
