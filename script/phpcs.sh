# backup and be able to rollback first
phpcs --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php .
phpcbf --standard=PSR2 --tab-width=4 --encoding=utf-8 --ignore=*.tpl.php .
