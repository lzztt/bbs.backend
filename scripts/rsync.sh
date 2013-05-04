# do not sync config.php, static/data
rsync -ave 'ssh -p 8022' controller dataobject lzx languages modules route.php portal.php scripts themes web@houstonbbs.com:www.piebbs.com/ --exclude=alexa.tpl.php --exclude=.svn
rsync -ave 'ssh -p 8022' static/themes web@houstonbbs.com:www.piebbs.com/static/ --exclude=.svn
