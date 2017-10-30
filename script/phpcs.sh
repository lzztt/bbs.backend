# backup and be able to rollback first
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php .
phpcbf --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php .
# error only
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php --error-severity=1 --warning-severity=8 .

