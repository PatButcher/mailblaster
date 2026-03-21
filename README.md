
# MAILBLASTER

This application was inspired by the corruption of Greater Manchester Police.


## About


Automated mass-mailer email system with SMPT API credential rotation system and recurring campaigns.

 · Campaigns
 
 · Single Emails
 
 · SMTP Testing
 
 · Contacts Lists with Tags
 
 · CSV Contact Import / Export
 
 · Email Logging with CSV Export




## HOW TO USE

### ARTISAN COMMANDS
campaigns:queue-recurring

email:process-queue


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

