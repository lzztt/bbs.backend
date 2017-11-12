# do not sync config.php, static/data
# rsync -ave 'ssh' client/fonts web@houstonbbs.com:bbs/client/
rsync -ave 'ssh' client/themes/roselife web@houstonbbs.com:bbs/client/themes/
rsync -ave 'ssh' client/app web@houstonbbs.com:bbs/client/ --exclude='*.__HEAD__'

rsync -ave 'ssh' lib/lzx web@houstonbbs.com:bbs/lib/
rsync -ave 'ssh' script web@houstonbbs.com:bbs/
rsync -ave 'ssh' server web@houstonbbs.com:bbs/ --exclude='Config.php'

rsync -ave 'ssh' lib/lzx web@houstonbbs.com:bbs/lib/ --delete
rsync -ave 'ssh' script web@houstonbbs.com:bbs/ --delete
rsync -ave 'ssh' server web@houstonbbs.com:bbs/ --exclude='Config.php' --delete
