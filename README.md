# Kytschi's Tengu

Decided to open source this monster. It's a very feature rich CMS written in Phalcon with other systems bolted on.

This is public for now just to illustrate to those looking at me for work the types of systems I build so I'd hold off actually using it till I get it into a more "generic" state for people.

If you after a more lightweight CMS go with my other project https://github.com/kytschi/dumb-dog

## Setup

### Creating the tengu.pub file for encryption
Tengu uses openssl for encrypting various pieces of data stored within the system. It looks for a PUB file called `tengu.pub` located in the secure folder.

If your on Linux you can do this by running the following command.

** DON'T SET A PASSWORD **

```bash
ssh-keygen -f secure/tengu
```

### Dump folder
The dump folder is used to store any file uploaded in tengu so it must have permissions by the webserver user to write into it. For example you can set the permissions as follows,

```bash
sudo chown -R www-data public/dump
sudo chmod -R 755 public/dump
```

Make sure your webserver never serves the `secure` folder and feel free to backup the files generated. Even move the private key `tengu` if you like.

## Migrations

### Create
vendor/bin/phalcon-migrations generate --config=migrations.php

### Run
vendor/bin/phalcon-migrations run --config=migrations.php