git clone ssh://mika@jessie/data/git/bbs.git frontend
cd frontend/
git filter-branch --prune-empty --subdirectory-filter client master
git remote set-url origin ssh://mika@jessie/data/git/bbs_frontend.git
git push -u origin master

cd ..
git clone ssh://mika@jessie/data/git/bbs.git database
cd database/
git filter-branch --prune-empty --subdirectory-filter db master
git remote set-url origin ssh://mika@jessie/data/git/bbs_db.git
git push -u origin master

cd ..
git clone ssh://mika@jessie/data/git/bbs.git backend
cd backend/
# git filter-branch --index-filter 'git rm --cached --ignore-unmatch -r client db nohup.out' -- --all
git filter-branch --index-filter 'git rm --cached --ignore-unmatch -r lib/coin-slider-04042010 lib/coin-slider-04042010.zip lib/coin-slider-04202013 lib/coin-slider-04202013.zip lib/fontello-ea983922 lib/fontello-ea983922.zip lib/html5imageupload lib/markitup-1.1.14 lib/markitup-1.1.14.zip lib/markitup-latest lib/markitup.zip lib/pica.js lib/star-rating-v4.11 lib/star-rating-v4.11.zip lib/superfish-1.4.8 lib/superfish-1.4.8.zip lib/superfish-1.7.2 lib/superfish-1.7.2.zip lib/superfish-master lib/superfish-master.zip client db nohup.out server/language server/module' -- --all
git remote set-url origin ssh://mika@jessie/data/git/bbs_backend.git
git push -u origin master
git status
