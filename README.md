# toggl-jira

Download time entries from Toggl and use them to log time in Jira

## Features

* multiple configurations for multiple jira
* White list project keys for api calls (`PROJECTS` in config file comma separated)
* Pass the right starting time from Toggl time entry to jira
* Displays executed commands prior to running them
* Hides password from the command line

## Setup

```bash
git clone git@github.com:gableroux/toggl-jira.git
cd toggl-jira
cp config/config.properties.dist config/config.properties
cp config/.env.example .env.YOUR_CONFIG
cp docker-compose.example.yml docker-compose.YOUR_CONFIG.yml
```

## Configure

1. Change values in your `config/config.YOUR_CONFIG.properties`.
2. Set credentials in `.env.YOUR_CONFIG`
3. Update config path in `docker-compose.YOUR_CONFIG.yml`

## General usage

Run run.sh with a start and end date, like this:

```bash
docker-compose -f docker-compose.yml -f docker-compose.tlm.yml run --rm php /app/run.sh ./run.sh 2015-09-29 2015-09-30
```
This will use `config.properties` by default. You can also do the following:

```bash
docker-compose -f docker-compose.yml -f docker-compose.tlm.yml run --rm php 2015-09-29 2015-09-30
```
*Running the above command will output something that looks like this:

```
==== BASH SCRIPT START ============================
#!/bin/bash

echo 'KEY-123 2016-12-07T00:01:00.000-0600 0.50h;'
curl -u your_username:$(cat /home/your_user/.jira-pass) -X POST -H "Content-Type: application/json" \
--data '{"started":"2016-12-07T00:01:00.000-0600","timeSpent":"0.50h","author":{"self":"https:\/\/some_client.atlassian.net\/rest\/api\/2\/user?username=your_username"}}' \
https://some_client.atlassian.net/rest/api/2/issue/KEY-123/worklog
echo ""
echo ""
==== BASH SCRIPT END ==============================
Script saved to some_client_2016-12-03_2016-12-07_2016-12-08_17h41m.sh
Press any key to submit the time to Jira (or Crtl+C to exit) ...
```

Press any key to submit the time to Jira. *todo: use a (Y/n) instead, it's safer* and use a `--no-input` parameter or something like that to auto accept.

You will then see something that looks like this:

```
KEY-456 2016-12-07T00:01:00.000-0600 2.25h;
{"self":"https://some_client.atlassian.net/rest/api/2/issue/19794/worklog/13715","author":{"self":"https://some_client.atlassian.net/rest/api/2/user?username=your_username","name":"your_username","key":"your_username","emailAddress":"you@your_email.com","avatarUrls":{"48x48":"https://some_client.atlassian.net/secure/useravatar?ownerId=your_username&avatarId=xxxxx","24x24":"https://some_client.atlassian.net/secure/useravatar?size=small&ownerId=your_username&avatarId=xxxxx","16x16":"https://some_client.atlassian.net/secure/useravatar?size=xsmall&ownerId=your_username&avatarId=xxxxx","32x32":"https://some_client.atlassian.net/secure/useravatar?size=medium&ownerId=your_username&avatarId=xxxxx"},"displayName":"Your actual Name","active":true,"timeZone":"America/Montreal"},"updateAuthor":{"self":"https://some_client.atlassian.net/rest/api/2/user?username=your_username","name":"your_username","key":"your_username","emailAddress":"you@your_email.com","avatarUrls":{"48x48":"https://some_client.atlassian.net/secure/useravatar?ownerId=your_username&avatarId=xxxxx","24x24":"https://some_client.atlassian.net/secure/useravatar?size=small&ownerId=your_username&avatarId=xxxxx","16x16":"https://some_client.atlassian.net/secure/useravatar?size=xsmall&ownerId=your_username&avatarId=xxxxx","32x32":"https://some_client.atlassian.net/secure/useravatar?size=medium&ownerId=your_username&avatarId=xxxxx"},"displayName":"Your actual Name","active":true,"timeZone":"America/Montreal"},"created":"2016-12-08T14:29:30.204+0000","updated":"2016-12-08T14:29:30.204+0000","started":"2016-12-07T06:01:00.000+0000","timeSpent":"2h 15m","timeSpentSeconds":8100,"id":"13715","issueId":"19794"}

KEY-123 2016-12-07T00:01:00.000-0600 0.50h;
{"errorMessages":["Issue Does Not Exist"],"errors":{}}

[and on and on...]
```

## Notes

Output has changed a little, I prefer per line output instead of a big wall of json text on one line.
keeping log files with parameters and current time which is quite handy if something went wrong.

### TODO

* Maybe rewrite most of the things here into something a bit more cleaner? Thinking of typescript, making a node module or something like that.
* Add client name to generated shell and log files

# License

MIT © [Matt Alexander](https://github.com/mattalexx)
