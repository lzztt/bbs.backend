function min
{
  type=$1;
  version=$2;
  java -jar /home/web/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar -v --type $type --charset utf-8 -o $type/min_$version.$type $type/all_$version.$type >> min_$version.log 2>&1;
}

time=`date +%s`;

list=`cat <<EOD
js/jquery.cookie.js
js/jquery.upload-1.0.2.js
js/jquery.markitup.js
js/jquery.markitup.bbcode.set.js
js/hoverIntent.js
js/superfish.js
js/coin-slider.js
js/jquery.MetaData.js
js/jquery.rating.js
js/main.js
EOD`
cat `echo $list | tr '\n' ' '` > js/all_$time.js
min js $time;

list=`cat <<EOD
css/default.css
css/system.css
css/houstonbbs.css
css/html-elements.css
css/advanced_forum.css
css/advanced_forum-structure.css
css/superfish.css
css/markitup.style.css
css/markitup.bbcode.css
css/coin-slider-styles.css
css/yp.css
css/jquery.rating.css
css/privatemsg-view.css
EOD`
cat `echo $list | tr '\n' ' '` > css/all_$time.css
min css $time;

sleep 1;
# gzip css and js min file
echo 'commands to create gzip files:'
for i in `ls */min_$time.*s`; do
	echo -e "\tgzip -c $i > $i.gz";
done
