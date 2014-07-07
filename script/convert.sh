for i in *.php; do
   class=`basename -s .php $i`
   dir=`basename -s .php $i | tr 'A-Z' 'a-z'`
   cd $dir
      #sed -i -e 's/class '$class' extends '$class'Ctrler/class '$class'Ctrler extends '$class'/' -e 's/ as '$class'Ctrler//' $i
      #mv $i ${class}Ctrler.php
      for j in *Ctrler.php; do
         subclass=`echo $j | sed 's/Ctrler.php$//'`
         #cp $i $subclass.php
         sed -i -e 's/class '$subclass' extends '$class'Ctrler/class '$subclass'Ctrler extends '$class'/' -e 's/ as '$class'Ctrler//' $j
         #mv ${subclass}.php ${subclass}Ctrler.php
         #sed -i 's/class '$class'/class '$subclass'/g' $subclass.php
      done
   cd ..
done
