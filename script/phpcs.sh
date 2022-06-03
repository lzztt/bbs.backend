# backup and be able to rollback first
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php,vendor/,gen/ .
phpcbf --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php,vendor/,gen/ .
# error only
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php,vendor/,gen/ --error-severity=1 --warning-severity=8 .

