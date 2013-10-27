# do not sync config.php, static/data

rsync $@ -ave 'ssh -p 8022' server web@houstonbbs.com:www.houstonbbs.com/ --exclude=alexa.tpl.php --exclude=config.php --exclude=.svn
rsync $@ -ave 'ssh -p 8022' lib/lzx web@houstonbbs.com:www.houstonbbs.com/lib/ --exclude=.svn
rsync $@ -ave 'ssh -p 8022' client/themes web@houstonbbs.com:www.houstonbbs.com/client/ --exclude=.svn
