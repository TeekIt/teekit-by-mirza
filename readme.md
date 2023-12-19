**How To Deploy Teek it On A Cloud Server With Ubuntu Terminal**
-----
Web App:
1) composer install.
2) Create & modify the .env file.
3) Change the owner of the following files:-
	a) chown -R www-data:www-data bootstrap/
	b) chown -R www-data:www-data storage/

DB:
1) Create a database cluster on Digital Ocean.
2) Modify the .env file according to the new database cluster credentials.
3) Keep DB_HOST=localhost in the .env file.
4) Now Install PhpMyadmin:
	https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-on-ubuntu-20-04
5) Import your local DB into the live DB.

**How To Resolve Digital Ocean Droplet Console Time Out Error**
-----
First check the set of rules of your default UFW firewall:<br>
sudo ufw status numbered

Now check the default port of your SSH:<br>
grep -i port /etc/ssh/sshd_config

Now if UFW is not allowing SSH port then please add it in UFW rules:<br>
sudo ufw allow ssh_port_number (type your SSH port default number here)

Now restart your SSH:<br>
sudo systemctl start ssh

**How To Install MeiliSearch**
-----
How To Install On Localhost:<br>
Reference:<br>
https://docs.meilisearch.com/learn/getting_started/quick_start.html#setup-and-installation

Goto the reference Doc & jump to the Local installation section. From there you can select the method of installation according to your own OS. After downloading meilisearch pkg just run it & see if it's working or not. 
Default port for meilisearch is localhost:7700<br>
After running meilisearch on your Windows execute the following commands:<br>
php artisan scout:import "App\Products"<br>
php artisan scout:sync-index-settings<br>

How To Install On Production:<br>
Reference:<br>
https://postsrc.com/posts/setting-up-meilisearch-on-production-ubuntu-for-laravel-project
https://docs.meilisearch.com/learn/cookbooks/running_production.html#a-quick-introduction

1) curl -L https://install.meilisearch.com | sh
2) Enter: ./meilisearch --help (In case of any help required)
3) nano /etc/systemd/system/meilisearch.service
Paste the following script, do note that you will have to change the master key into your own securely defined master key:-

[Unit]
Description=MeiliSearch
After=systemd-user-sessions.service

[Service]
Type=simple
ExecStart=/usr/bin/meilisearch --http-addr 127.0.0.1:7700 --env production --master-key 0000-kxkkkk-shhhh-shhhhhhh

[Install]
WantedBy=default.target

4) systemctl enable meilisearch
5) systemctl start meilisearch
6) systemctl status meilisearch (Check that the service is actually running)
7) Now connect Laravel with Meilisearch by adding the following into your .env file:<br>
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=masterKey

8) Run the following commands:<br>
php artisan scout:sync-index-settings<br>
php artisan scout:import "App\Products"<br>


