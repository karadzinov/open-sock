git clone git@int.sentice.com:php/sapo.git

cd sapo

touch README.md

git add README.md

git commit -m "add README"

git push -u origin master

To run the server please do the following

cd working_dir
php bootstrap/server.php


To get working please do the following:
DEBUGBAR_ENABLED=true to .env
Change config.debugbar.collectors.auth to false