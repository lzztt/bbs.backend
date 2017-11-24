# backup and be able to rollback first
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php,vendor/ .
phpcbf --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php,vendor/ .
# error only
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php,vendor/ --error-severity=1 --warning-severity=8 .

