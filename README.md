# Kytschi's Tengu

Decided to open source this monster. It's a very feature rich CMS written in Phalcon with other systems bolted on.

This is public for now just to illustrate to those looking at me for work the types of systems I build so I'd hold off actually using it till I get it into a more "generic" state for people.

If you after a more lightweight CMS go with my other project https://github.com/kytschi/dumb-dog

## Migrations

### Create
vendor/bin/phalcon-migrations generate --config=migrations.php

### Run
vendor/bin/phalcon-migrations run --config=migrations.php