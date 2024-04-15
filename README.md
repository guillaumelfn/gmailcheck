# gmailcheck
Check your gmail for a keyword using PHP and alerting a Google Chat Rooms

# install

First of all, Go on your google cloud console
- Create a project
- Enable Google API
- Create a service account in view only + Cut/paste the ClientID
- Create a json key from this service account (That you will add in your project directory)
- On your Google Worspace Admin, enable Domain Wide Delegation using the client ID
- Create a webhook for Google Chat

On your shell,
composer require google/apiclient:^2.0
Install the json file (name it key.json for instance)

Then install your script, update it with :
- Webhook for google chat
- ClientID

# run
php gmailcheck.php

