cd controller/
for i in *.php; do i=`basename $i .php`; d=`echo $i | tr 'A-Z' 'a-z'`; git mv $d/${i}Ctrler.php $d/Handler.php; done
for i in */*Ctrler.php; do d=`echo $i | tr 'A-Z' 'a-z' | sed 's/.\{10\}$//'`; mkdir -p $d; git mv $i $d/Handler.php; done
for i in *.php; do d=`basename $i .php | tr 'A-Z' 'a-z'`; git mv $i $d; done
cd ../api/
for i in *API.php; do d=`echo $i | tr 'A-Z' 'a-z' | sed 's/.\{7\}$//'`; mkdir $d; git mv $i $d/Handler.php; done
git status
