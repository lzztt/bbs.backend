# do not sync config.php, static/data

rsync $@ -ave 'ssh -p 3355' server web@houstonbbs.com:www.houstonbbs.com/ --exclude=alexa.tpl.php --exclude=Config.php --exclude=.svn
rsync $@ -ave 'ssh -p 3355' lib/lzx web@houstonbbs.com:www.houstonbbs.com/lib/ --exclude=.svn
rsync $@ -ave 'ssh -p 3355' client/themes web@houstonbbs.com:www.houstonbbs.com/client/ --exclude=.svn
