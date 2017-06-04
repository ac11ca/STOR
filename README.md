#Website Template
* * *

Symfony 3 PHP 7.0 Web App Template

##Suggested setup

The following commands will help configure and setup a brand new AWS instance. In this case, our example users username will be taha and the website will be called templatetaha.cyint.technology.

    useradd -m taha                                                                                        # creates a user named taha. The -m flag creates a home directory named taha as well
    usermod -a -G sudo taha                                                                                # Adds user taha to sudo group so they can invoke sudo
    passwd taha 			                                                                               # Specify password for taha user
    chage -d0 taha			                                                                               # (optional, forces user to reset their password on next login
    chsh -s /bin/bash taha 	                                                                               # Set the default shell to bash for user taha
    sudo su taha 			                                                                               # Switch to the taha user
    cd ~/					                                                                               # Navigate to /home/taha
    ssh-keygen 				                                              				                   # generate ~/.ssh folder. You can use all of the defaults here to generate a server key pair for taha
    cd ~/.ssh                                                             				                   # Navigate to the ~/.ssh directory
    vim authorized_keys                                                   				                   # create the authorized keys file, paste the public key from your local machine in here
    cd ~/                                                                      			                   # go back to /home/taha
    git clone git@bitbucket.org:cyinttechnologies/symfonyboilerplate.git templatetaha                      # Be sure to use the SSH associated with YOUR repo access
    exit                                                                                                   # this should drop you back to the root account
    ./template-scripts/swap-setup.sh                                                                       # increases RAM to 5GB by using the hard disk (swapfile) to store extra ram overflow
    ./template-scripts/initial-setup.sh                                                                    # This will install composer and allow you to run composer install
    git remote -v 																		                   # lists your remotes
    git remote rm origin 																                   # removes the origin remote, disconnecting your code from the template repository   
    git remote add origin https://dfredriksenCYINT@bitbucket.org/cyinttechnologies/symfonyboilerplate.git  # Adds the new repository URL as the origin. Use HTTPS so that you have to enter your password to push. Make sure to link to the project repository and not the template repository. If you were to have cloned this on your local machine, using ssh and ssh keys would allow you to push without a password. Make sure to use the HTTPS url associated with YOUR bitbucket access.   
    ./scripts/server-setup.sh                                                                              # Installs mysql and all template dependencies)

    # Create a mysql root user and password, as well as a phpmyadmin user/password using LastPass to generate and store the secure password.
    # When the MySql hardening script is run, Select N for using the VALIDATE PASSWORD plugin
    # N for changing the password
    # Y for remove anonymous users 
    # Y for disable remote login
    # Y to remove test database
    # Y to reload priv table
    service nginx stop; letsencrypt certonly															   # generate free ssl cert for your site. Enter the site name when prompted 
    cd /etc/nginx/ 
    mkdir ssl
    ln -s /etc/letsencrypt/live/templatetaha.cyint.technology/fullchain.pem ./templatetaha.crt 			   # This creates a shortcut to your generated cert file. Be sure to replace temmplatetaha.cyint.technology with the name of the domain you used for the certificate. 
    ln -s /etc/letsencrypt/live/templatetaha.cyint.technology/priivkey.pem ./templatetaha.key              # link to the generated certificate private key
    cd ../sites-available                                                                                  # Move to the sites-available folder to prepare to add virtual site
    vim templatetaha.cyint.technology                                                                      # Replace with name of your site

Paste the following configuration:

    server {
        listen 80;
        server_name templatetaha.cyint.technology;
        return      301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl;
        server_name templatetaha.cyint.technology;
        root /home/taha/templatetaha/web;

        ssl_certificate /etc/nginx/ssl/templatetaha.crt;
        ssl_certificate_key /etc/nginx/ssl/templatetaha.key;

        location / {
            # try to serve file directly, fallback to app.php
            try_files $uri /app_dev.php$is_args$args;
        }
        # DEV
        # This rule should only be placed on your development environment
        # In production, don't include this and don't deploy app_dev.php or config.php
        location ~ ^/(app_dev|config)\.php(/|$) {
            fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
        }

            location /phpmyadmin {
                   root /usr/share/;
                   index index.php index.html index.htm;
                   location ~ ^/phpmyadmin/(.+\.php)$ {
                           try_files $uri =404;
                           root /usr/share/;
                       fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
                           fastcgi_index index.php;
                           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                           include /etc/nginx/fastcgi_params;
                   }
                   location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
                           root /usr/share/;
                   }
            }
 
            location /phpMyAdmin {
               rewrite ^/* /phpmyadmin last;
            }


        error_log /var/log/nginx/templatetaha_error.log;
        access_log /var/log/nginx/templatetaha_access.log;
    }

Don't forget to replace all instances of templatetaha.cyint.technology with YOUR sites domain as well as the root path, error logs, and SSL certificates

    cd ../sites-enabled									                         # we are going to add a simlink here to your site
    ln -s /etc/nginx/sites-available/templatetaha.cyint.technology ./            # adds a simlink to your virtual site file in sites-available to sites-enabled
    service nginx restart                                                        # restart nginx, should not output anything
    cd /home/taha                                                                # go to your user directory
    vim app/config/parameters.yml.dist                                           # modify the default parameters for this project by using the db credentials you stored earlier. host should be localhost for now, come up with a good name for the db such as <projectname>dev. You might have to recreate this file if this is an existing repository 
    composer install                                                             # This will prompt you for the database name and credentials, make up an appropriate name and use the credentials you created for mysql before. The host should be localhost
    git rm --cached app/config/parametes.yml.dist                                # This will remove the file from the repo but preserve it on your filesystem. It is already in your .gitignore so you don't have to worry about it accidentally being checked in again. Only run this if this is the first time the repo is created
    sudo chown taha:www-data ./ -R  											 # use your proper username in place of Taha, this resets the permissions
    sudo chmod 775 ./ -R 														 # Let owner and group read/write while restricting public to read and execute
    ./scripts/resetdb.sh                                                         # This will create the db, create the schema, and run the migration of the default admin user (username:  admintest, password:  dollydolly)
    npm install                                                                  # Install node modules
    ./node_modules/.bin/gulp                                                     # Compile libraries, less, assets, and js

Now, edit the crontab to add an automatic renew request for the ssl cert once a month. 

    crontab -e


To setup the crontab, place the following line:

    0 0 1 * * service nginx stop; letsencrypt renew; service nginx start

This will run the cert renew script at midnight on the first of every month.

And you are ready to rock and roll!
