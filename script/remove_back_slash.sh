sed -Ei 's!\\(CURLOPT|FILE|FILTER|GLOB|IMAGETYPE|JSON|LOCK|PHP|UPLOAD)!\1!g' `cat /tmp/file.1`

grep -E '\\[A-Za-z]{3,}' `find . -name '*.php'` | grep -Ev '(namespace|use|@|HandlerRouter.php|\\n|\\t| case )' | grep -Eo '\\[A-Za-z0-9_]{3,}'  | sort | uniq | sed 's/^.//' | tr '\n' '|'
grep -Er '\\(DateTime|ErrorException|Exception|Imagick|ImagickDraw|ImagickPixel|InvalidArgumentException|SplObjectStorage|Throwable)' . | awk -F : '{print $1}' | sort | uniq > ~/files.log 

for i in `cat ~/files.log`; do line=`grep -Eo '\\(DateTime|ErrorException|Exception|Imagick|ImagickDraw|ImagickPixel|InvalidArgumentException|SplObjectStorage|Throwable)' $i | sort | uniq | sed -e 's/^/use /' -e 's/$/;/' | tr '\n' ' ' | sed 's/ $//'`; echo awk "'NR==5{print \"$line\"}7'" $i "> $i.new"; done
for i in `cat ~/files.log`; do line=`grep -Eo '\\(DateTime|ErrorException|Exception|Imagick|ImagickDraw|ImagickPixel|InvalidArgumentException|SplObjectStorage|Throwable)' $i | sort | uniq | sed -e 's/^/use /' -e 's/$/;/' | tr '\n' ' ' | sed 's/ $//'`; echo awk "'NR==5{print \"$line\"}7'" $i "> $i.new"; done | bash

for i in `cat ~/files.log`; do diff $i $i.new; done
for i in `cat ~/files.log`; do mv $i.new $i; done

grep 'Exception; use' `cat ~/files.log` | awk -F : '{print $1}' | sort | uniq > ~/files.log2
for i in `cat ~/files.log2`; do perl -0777 -i -pe 's/; use /;\nuse /igs' $i; done
sed -Ei 's!\\(DateTime|ErrorException|Exception|Imagick|ImagickDraw|ImagickPixel|InvalidArgumentException|SplObjectStorage|Throwable)!\1!g' `cat ~/files.log`
