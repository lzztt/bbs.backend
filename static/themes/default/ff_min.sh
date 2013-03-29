 time=`date +%s`
 cat js/jquery-1.8.3.js js/FFcoin-slider.js  js/FFmain.js > js/fyfm-all-$time.js
 java -jar /home/web/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar -v --type js --charset utf-8 -o js/fyfm-min-$time.js js/fyfm-all-$time.js
 gzip -c js/fyfm-min-$time.js > js/fyfm-min-$time.js.gz
