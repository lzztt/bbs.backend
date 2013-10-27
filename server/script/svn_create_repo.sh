svnadmin create /data/svn/houstonbbs

svn mkdir -m 'create trunk' file:///data/svn/houstonbbs/trunk
svn mkdir -m 'create tags' file:///data/svn/houstonbbs/tags
svn mkdir -m 'create branches' file:///data/svn/houstonbbs/branches

svn import -m 'initial import' /home/web/www.houstonbbs-test.com/trunk file:///data/svn/houstonbbs/trunk
