# do not sync config.php, static/data
rsync -ave 'ssh -p 3355' client/fonts web@houstonbbs.com:bbs/client/
rsync -ave 'ssh -p 3355' client/themes/roselife web@houstonbbs.com:bbs/client/themes/
rsync -ave 'ssh -p 3355' lib/lzx web@houstonbbs.com:bbs/lib/
rsync -ave 'ssh -p 3355' script web@houstonbbs.com:bbs/
rsync -ave 'ssh -p 3355' server web@houstonbbs.com:bbs/ --exclude='Config.php' --exclude='alexa.houston.tpl.php' --exclude='alexa.dallas.tpl.php' 
