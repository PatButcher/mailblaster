
# MAILBLASTER


## About


Automated mass-mailer email system with SMPT API credential rotation system and recurring campaigns.

 · Campaigns
 
 · Single Emails
 
 · SMTP Testing
 
 · Contacts Lists with Tags
 
 · CSV Contact Import / Export
 
 · Email Logging with CSV Export



## TODO

1. Fix credential rotation - currently selects one and does not transition to next smtp server profile on limit hit.
2. Setup Mailing Lists - 
3. Fix email log date searching
4. Test SYNC UNSUBSCRIBED CONTACTS command



## HOW TO USE

### ARTISAN COMMANDS

campaigns:queue-recurring

email:process-queue

brevo:sync-unsubscribed-contacts


### .env
```
#MAIL_MAILER=log
MAIL_MAILER=smtp

#QUEUE_CONNECTION=sync
QUEUE_CONNECTION=redis
```


### create cronjobs

```
# START QUEUE ON RESTART
@reboot /usr/bin/php '/your/local/webroot/mailblaster/artisan' 'queue:work' --no-interaction --quiet

# QUEUE RECURRING CAMPAIGNS - (minutely)
5 * * * * /usr/bin/php '/your/local/webroot/mailblaster/artisan' 'campaigns:queue-recurring' --no-interaction --quiet

# SEND QUEUED EMAILS - (HALF-HOURLY)
0,15,30,45 * * * * /usr/bin/php '/your/local/webroot/mailblaster/artisan' 'email:process-queue' --no-interaction --quiet

# SYNC UNSUBSCRIBED CONTACTS (untested)
#2 0 * * * /usr/bin/php '/usr/share/angie/lara-mailblaster/artisan' 'brevo:sync-unsubscribed-contacts' --no-interaction --quiet
```

### start email process queue manually
```
cd /your/local/webroot/mailblaster && php artisan queue:work
```


## Contributing

Feel free to fork.


## Code of Conduct

Don't break the law, unless the law is breaking you or itself.


## Security Vulnerabilities

Lots, probably.


## License

The Mailblaster framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

