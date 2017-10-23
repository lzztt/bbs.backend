# backup and be able to rollback first
perl -0777 -i.orig -pe 's/{\n\n/{\n/igs' server/Service.php
for i in `find . -name '*.php' | grep -v 'tpl.php'`; do echo perl -0777 -i -pe 's/{\n\n/{\n/igs' $i; done
for i in `find . -name '*.php' | grep -v 'tpl.php'`; do perl -0777 -i -pe 's/{\n\n/{\n/igs' $i; done
