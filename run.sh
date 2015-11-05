#!/bin/bash

set -e

START_DATE="$1"
END_DATE="$2"
PROP_FILE="config.properties"

function get_property
{
    PROP_KEY=$1
    PROP_VALUE=`cat $PROP_FILE | grep "$PROP_KEY" | cut -d'=' -f2`
    PROP_VALUE=${PROP_VALUE//[[:blank:]]/}

    if [ ! "$PROP_VALUE" ]; then
        echo "Configuration option $PROP_KEY is required"
        exit 1
    fi

    echo $PROP_VALUE
}

TOGGL_USER=$(get_property "TOGGL_USER")
TOGGL_WORKSPACE_ID=$(get_property "TOGGL_WORKSPACE_ID")
TOGGL_KEY_FILE=$(get_property "TOGGL_KEY_FILE")

KEY=$(head -n 1 "$TOGGL_KEY_FILE");

DATE_REGEX="[0-9]{4}-[0-9]{2}-[0-9]{2}"
[[ $START_DATE =~ $DATE_REGEX && $END_DATE =~ $DATE_REGEX ]] || {
    echo "Usage: ./run.sh 2014-01-01 2014-01-31"
    exit 1
}

DAY=${END_DATE:8:2}
((DAY++))
DAY=`printf %02d $DAY`
DAY_AFTER_END_DATE="${END_DATE:0:8}${DAY}"

TIME="T00%3A01%3A00-05%3A00" # T00:01:00-05:00 (12:01 AM Eastern)

script_name="insert_entries-${START_DATE}-${END_DATE}.sh"
toggl_url="https://www.toggl.com/api/v8/time_entries"
toggl_url+="?user_agent=${TOGGL_USER}"
toggl_url+="&workspace_id=${TOGGL_WORKSPACE_ID}"
toggl_url+="&start_date=${START_DATE}${TIME}"
toggl_url+="&end_date=${DAY_AFTER_END_DATE}${TIME}"

# Run script
#echo "curl -s -u \"$KEY:api_token\" -X GET \"$toggl_url\""
#curl -s -u "$KEY:api_token" -X GET "$toggl_url"
code=$(curl -s -u "$KEY:api_token" -X GET "$toggl_url" | php toggl_entries.php)

# Save to file
echo "$code" > "$script_name"

# Output code to terminal
echo "Called URL: $toggl_url"
echo ==== BASH SCRIPT START ============================
echo "$code"
echo ==== BASH SCRIPT END ==============================

# Message to user
echo "Script saved to $script_name"

# Upload to Jira
echo "Press any key to submit the time to Jira (or Crtl+C to exit) ..."
read -n 1
echo "Submitting ..."
. "$script_name"
