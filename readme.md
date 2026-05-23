# Deployment Guide
## Localhost Deployment

**Prerequisites:**
* <a href="https://learn.microsoft.com/en-us/windows/wsl/install" target="_blank">WSL</a>
* Docker
* Docker Compose
---
**Execute the following commands in WSL/Linux based terminal:**
1. Prepare the directory and clone the repository:
```bash
   sudo mkdir -p /var/www
   cd /var/www
   sudo git clone https://github.com/mirza-organization/teekit-by-mirza.git
   cd teekit-by-mirza
   ```

2. Configure the environment:
```bash
   sudo cp .env.example .env
   ```

3. Copy the `teekit_database.sql` file to the `docker-compose/mysql` directory.

4. Read the `docker-compose/entrypoint.sh` file carefully and follow all necessary commands.

5. Build and start the container:
```bash
   sudo docker compose up -d --build
   ```

6. Navigate back to the "www" directory and set up the global Nginx proxy:
```bash
   cd ../
   sudo mkdir -p nginx-docker/conf.d
   cd nginx-docker/
   sudo nano docker-compose.yml
   ```

7. Add the following configuration to the Nginx `docker-compose.yml` file:
```yaml
   services:
     nginx:
       image: nginx:alpine
       container_name: global-nginx
       restart: unless-stopped
       ports:
         - "80:80"
       volumes:
         - ./conf.d:/etc/nginx/conf.d
         - /var/www:/var/www
       networks:
         - nginx-network

   networks:
     nginx-network:
       # Ensure this network exists. If not, create it manually:
       # docker network create nginx-network
       external: true
   ```

8. Save and exit (`Ctrl + X` => `Y` => `Enter`).

9. Create the Nginx server block for your Laravel app:
```bash
   cd conf.d/
   sudo nano teekit.conf
   ```

10. Add the following server block (customize the domain according to your needs):
```nginx
    server {
        listen 80; # 443 for HTTPS

        server_name teekit-by-mirza.docker; # Use server Public IP in production if no domain available 

        root /var/www/teekit-by-mirza/public;

        index index.php;

        error_log  /var/log/nginx/error.log;
        access_log /var/log/nginx/access.log;

        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass teekit-by-mirza:9000;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info;
        }
        
        location / {
            try_files $uri $uri/ /index.php?$query_string;
            gzip_static on;
        }
    }
```

11. Navigate back to the Nginx root directory (nginx-docker) and start the container:
```bash
    cd ../
    sudo docker compose up -d --build
```

12. Map the domain locally (Windows Only, for iOS the location is different):
* Navigate to `C:\Windows\System32\drivers\etc\hosts` open in **Notepad**.
* Now open another **Notepad** as Administrator (search from taskbar).
* Copy the contents of the `hosts` file into the administrator **Notepad**, and add the following lines at the bottom of the file:
```text
      # Custom generated Hosts
      127.0.0.1       teekit-by-mirza.docker
  ```
* Save the administrator **Notepad** and overwrite the default `hosts` file in `C:\Windows\System32\drivers\etc`.

13. THaaa-Daaa that's it 😅. Now visit `http://teekit-by-mirza.docker`.

--- 
## Production Deployment

**Prerequisites:**
* VPS with Ubuntu LTS
* Docker
* Docker Compose

1. Follow **Steps 1 through 10** from the [Localhost Deployment](https://github.com/mirza-organization/teekit-by-mirza/tree/staging#localhost-deployment) section & **skip Step 5**.

2. Create a new user on production before building the containers:
```bash
   # Create a new group (DO NOT CHANGE GROUPNAME)
   sudo groupadd -g 1000 mirza

   # Create a new user, assign them to the group, & create their home directory 
   # (DO NOT CHANGE USERNAME)
   sudo useradd -u 1000 -g mirza -m -s /bin/bash mirza

   # Set a secure password for this new user
   sudo passwd mirza
   ```

3. Build and start the Laravel application:
```bash
   cd /var/www/teekit-by-mirza
   sudo docker compose up -d --build
   ```

4. Build and start the Nginx proxy:
```bash
   cd /var/www/nginx-docker
   sudo docker compose up -d --build
   ```

5. THaaa-Daaa we are done😅. Visit your domain.
---
## **How To Resolve Digital Ocean Droplet "Console Time Out Error"**
1. Access the Droplet from "Recovery console".

2. Now check the set of rules of your default UFW firewall:
```bash
   sudo ufw status numbered
   ```

3. Now check the default port of your SSH:
```bash
   grep -i port /etc/ssh/sshd_config
   ```

4. Now if UFW is not allowing SSH port then please add it in UFW rules (type your SSH port default number here):
```bash
   sudo ufw allow ssh_port_number 
   ```

5. Now restart your SSH:
```bash
   sudo systemctl start ssh
   ```
