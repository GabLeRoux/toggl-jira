#!/usr/bin/env bash

set -e
#set -x

START_DATE="$1"
END_DATE="$2"

PROP_FILE=config/${PROP_FILE:-"config.properties"}
FILES_DESTINATION=${FILES_DESTINATION:-"./insert_entries"}

function get_property
{
    PROP_KEY=$1
    PROP_VALUE=`cat $PROP_FILE | grep "$PROP_KEY" | cut -d'=' -f2`
    PROP_VALUE=${PROP_VALUE//[[:blank:]]/}

    if [[ ! "$PROP_VALUE" ]]; then
        echo "Configuration option $PROP_KEY is required"
        exit 1
    fi

    echo $PROP_VALUE
}

TOGGL_USER=$(get_property "TOGGL_USER")
TOGGL_WORKSPACE_ID=$(get_property "TOGGL_WORKSPACE_ID")
CLIENT_NAME=$(get_property "CLIENT_NAME") # only used for generated files names

KEY=${TOGGL_KEY}

DATE_REGEX="[0-9]{4}-[0-9]{2}-[0-9]{2}"
[[ $START_DATE =~ $DATE_REGEX && $END_DATE =~ $DATE_REGEX ]] || {
    echo "Usage: ./run.sh 2014-01-01 2014-01-31"
    exit 1
}

DAY=$(expr ${END_DATE:8:2} + 0)
((DAY++))
DAY=`printf %02d $DAY`
DAY_AFTER_END_DATE="${END_DATE:0:8}${DAY}"

# TODO: why is this hardcoded? please fix me!
TIME="T00%3A01%3A00-05%3A00" # T00:01:00-05:00 (12:01 AM Eastern)

script_name="${CLIENT_NAME}_${START_DATE}_${END_DATE}_`date +%Y-%m-%d_%Hh%Mm`.sh"
toggl_url="https://www.toggl.com/api/v8/time_entries"
toggl_url+="?user_agent=${TOGGL_USER}"
toggl_url+="&workspace_id=${TOGGL_WORKSPACE_ID}"
toggl_url+="&start_date=${START_DATE}${TIME}"
toggl_url+="&end_date=${DAY_AFTER_END_DATE}${TIME}"

# Run script
code=$(curl -s -u "$KEY:api_token" -X GET "$toggl_url" | php toggl_entries.php)

# Output code to terminal
echo "Called URL: $toggl_url"
echo ==== BASH SCRIPT START ============================
echo "$code"
echo ==== BASH SCRIPT END ==============================

# Save to file
echo "Script saved to ${FILES_DESTINATION}/${script_name}"
echo "$code" > "${FILES_DESTINATION}/${script_name}"

# Prompt user
echo "Press any key to submit the time to Jira (Crtl+C or Ctrl+D to exit) ..."
read -n 1

# Submit to Jira
log_file=${FILES_DESTINATION}/${script_name}.log
echo "Submitting to Jira ... (logging to ${log_file})"
. "${FILES_DESTINATION}/${script_name}" | tee ${log_file}
