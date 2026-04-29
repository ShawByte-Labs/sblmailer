# sbmailer - free marketing email portal

sbmailer is a lightweight, self hosted email marketing platform that lets you manage contacts, create mailing lists, and send campaigns using your own SMTP server.

It’s designed for developers, small businesses, and anyone who wants full control over their email campaigns without relying on third party services.

# Features
* Admin user setup
* Recipient/contact management
* Mailing list organisation
* Campaign creation & sending
* SMTP configuration (use your own email provider)
* Simple web based interface

# What it can be used for
* Sending newsletters to customers or subscribers
* Managing internal or community mailing lists
* Running marketing campaigns without monthly SaaS fees
* Testing email delivery using your own SMTP setup
* Hosting a private, self controlled email platform

# Initial public release of SBL Mailer ver 1.0.0

## Where to upload

Upload the `sblmailer` folder into your hosting web root:

httpdocs/sblmailer/

Your files should look like:

httpdocs/sblmailer/_footer.php
httpdocs/sblmailer/_header.php
httpdocs/sblmailer/app/
httpdocs/sblmailer/assets/
httpdocs/sblmailer/campaigns.php
httpdocs/sblmailer/config.php
httpdocs/sblmailer/database/
httpdocs/sblmailer/forgot_password.php
httpdocs/sblmailer/index.php
httpdocs/sblmailer/lists.php
httpdocs/sblmailer/login.php
httpdocs/sblmailer/logout.php
httpdocs/sblmailer/recipients.php
httpdocs/sblmailer/reset_password.php
httpdocs/sblmailer/send_campaign.php
httpdocs/sblmailer/settings.php
httpdocs/sblmailer/setup.php
httpdocs/sblmailer/users.php

## Setup

1. Create a MySQL database and set a user and a password
2. Import:

database/sblmailer.sql

3. Edit `config.php` with your database details.
4. Confirm `base_url` is correct:
5. Visit  your URL
6. Create your first admin user at yourdomain.com/setup.php
7. Log in.
8. Go to Settings and add SMTP details.
9. Send a test email.

## Security

This package includes `.htaccess` files to block direct browser access to:

- `config.php`
- `app/`
- `database/`
- `scripts/`

After creating the first admin user, you can delete `setup.php` for extra safety.
