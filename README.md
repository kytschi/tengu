# Kytschi's Tengu

One stop shop system for content management, customer relations, marketing and SEO, humman resources, project management and e-commerce.

**This is public for now just to illustrate to those looking at me for work the types of systems I build so I'd hold off actually using it till I get it into a more "generic" state for people.**

If you after a more lightweight CMS go with my other project https://github.com/kytschi/dumb-dog

## Requirements
* PHP 8.0 - PHP 8.1
* Mysql/Maria DB or equivalent
* php-phalcon5
* php-gd
* php-zip
* php-curl
* php-xml
* php-json
* php-mbstring
* php-intl
* php-mysql
* php-psr
* Redis (OPTIONAL but worth it for production server to cache)
* php-redis (OPTIONAL)
* geoiplookup (OPTIONAL, only if you want to lookup the country of origin for stats)

## Setup

### Creating the tengu.pub file for encryption
Tengu uses openssl for encrypting various pieces of data stored within the system. It looks for a PUB file called `tengu.pub` located in the secure folder.

If your on Linux you can do this by running the following command.

** DON'T SET A PASSWORD **

```bash
ssh-keygen -f secure/tengu
```

### Create the .env
Copy the ``.env.example`` to `.env` in the root folder and open it with your favourite editor.

Set the various values to that of your install.

#### Create an app key
Run something like the command below and copy the key to the `APP_KEY` in the `.env`.
```
openssl rand -base64 64
```

### Dump folder
The dump folder is used to store any file uploaded in tengu so it must have permissions by the webserver user to write into it. For example you can set the permissions as follows,

```bash
sudo chown -R www-data public/dump
sudo chmod -R 755 public/dump
```

Make sure your webserver never serves the `secure` folder and feel free to backup the files generated. Even move the private key `tengu` if you like.

### CRON
Tengu has a CRON it runs in the background to help it out, here's how to set it up.
```bash
* * * * * php -f /var/www/website/tengu/crons/Tengu.php > /dev/null 2>&1
```

## Migrations

### Create
vendor/bin/phalcon-migrations generate --config=migrations.php

### Run
vendor/bin/phalcon-migrations run --config=migrations.php

## Credits

Phalcon team for making an awesome framework! You guys rock with your co...errr, you rock.
https://phalcon.io/en-us

Postcodes.io for their lovely postcode API.
https://postcodes.io/

OpenStreetMap for their fantastic map service.
https://www.openstreetmap.org/#map=6/54.910/-3.432

Leaflet JS for their great map plugin.
https://leafletjs.com/

Bootstrap Icons for their amazing icons.
https://icons.getbootstrap.com/

jQuery, man where would be without jQuery?
https://jquery.com/