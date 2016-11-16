

# toggl-jira

Download time entries from Toggl and use them to log time in Jira

## Setup

1. Run:

        git clone git@github.com:mattalexx/toggl-jira.git
        cd toggl-jira
        echo "<TOGGL API KEY>" > ~/.toggl-key
        echo "<JIRA PASSWORD>" > ~/.jira-pass
        cp config.properties.dist config.properties
    
1. Change values in config.properties.

## General usage

Run run.sh with a start and end date, like this:

    ./run.sh 2015-09-29 2015-09-30

Running the above command will output something that looks like this:

    ==== BASH SCRIPT START ============================
    #!/bin/bash

    # PRJ-279 9/29/2015 2.12h
    curl -u malexander:$(cat /Users/mmalexander/.jira-pass) -X POST -H "Content-Type: application/json" \
    --data '{"started":"2015-09-29T00:01:00.000-0500","timeSpent":"2.12h","author":{"self":"https:\/\/myjira.atlassian.net\/rest\/api\/2\/user?username=malexander"}}' \
    https://myjira.atlassian.net/rest/api/2/issue/PRJ-279/worklog

    # PRJ-279 9/30/2015 3.27h
    curl -u malexander:$(cat /Users/mmalexander/.jira-pass) -X POST -H "Content-Type: application/json" \
    --data '{"started":"2015-09-30T00:01:00.000-0500","timeSpent":"3.27h","author":{"self":"https:\/\/myjira.atlassian.net\/rest\/api\/2\/user?username=malexander"}}' \
    https://myjira.atlassian.net/rest/api/2/issue/PRJ-279/worklog

    # PRJ-308 9/30/2015 3.96h
    curl -u malexander:$(cat /Users/mmalexander/.jira-pass) -X POST -H "Content-Type: application/json" \
    --data '{"started":"2015-09-30T00:01:00.000-0500","timeSpent":"3.96h","author":{"self":"https:\/\/myjira.atlassian.net\/rest\/api\/2\/user?username=malexander"}}' \
    https://myjira.atlassian.net/rest/api/2/issue/PRJ-308/worklog
    ==== BASH SCRIPT END ==============================
    Script saved to insert_entries-2015-09-29-2015-09-30.sh
    Press any key to submit the time to Jira (or Crtl+C to exit) ...
    
Press any key to submit the time to Jira.

You will then see something that looks like this:

     Submitting ...
    {"self":"https://myjira.atlassian.net/rest/api/2/issue/29711/worklog/14239","author":{"self":"https://myjira.atlassi
    an.net/rest/api/2/user?username=malexander","name":"malexander","key":"malexander","emailAddress":"Matt.Alexander@zz
    zzzzzzzz.com","avatarUrls":{"48x48":"https://myjira.atlassian.net/secure/useravatar?ownerId=malexander&avatarId=1290
    3","24x24":"https://myjira.atlassian.net/secure/useravatar?size=small&ownerId=malexander&avatarId=12903","16x16":"ht
    tps://myjira.atlassian.net/secure/useravatar?size=xsmall&ownerId=malexander&avatarId=12903","32x32":"https://myjira.
    atlassian.net/secure/useravatar?size=medium&ownerId=malexander&avatarId=12903"},"displayName":"Matt Alexander","acti
    ve":true,"timeZone":"America/Chicago"},"updateAuthor":{"self":"https://myjira.atlassian.net/rest/api/2/user?username
    =malexander","name":"malexander","key":"malexander","emailAddress":"Matt.Alexander@zzzzzzzzzz.com","avatarUrls":{"48
    x48":"https://myjira.atlassian.net/secure/useravatar?ownerId=malexander&avatarId=12903","24x24":"https://myjira.atla
    ssian.net/secure/useravatar?size=small&ownerId=malexander&avatarId=12903","16x16":"https://myjira.atlassian.net/secu
    re/useravatar?size=xsmall&ownerId=malexander&avatarId=12903","32x32":"https://myjira.atlassian.net/secure/useravatar
    ?size=medium&ownerId=malexander&avatarId=12903"},"displayName":"Matt Alexander","active":true,"timeZone":"America/Ch
    icago"},"created":"2015-10-09T03:37:49.646-0400","updated":"2015-10-09T03:37:49.646-0400","started":"2015-09-29T01:0
    1:00.000-0400","timeSpent":"2h 7m","timeSpentSeconds":7632,"id":"14239"}{"self":"https://myjira.atlassian.net/rest/a
    pi/2/issue/29711/worklog/14240","author":{"self":"https://myjira.atlassian.net/rest/api/2/user?username=malexan.....
    
    [and on and on...]
    
