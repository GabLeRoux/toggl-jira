#!/bin/bash

set -e

start_date="$1"
end_date="$2"

date_regex="[0-9]{4}-[0-9]{2}-[0-9]{2}"
[[ $start_date =~ $date_regex && $end_date =~ $date_regex ]] || {
    echo "Usage: ./run.sh 2014-01-01 2014-01-31"
    exit 1
}

key=$(head -n 1 key.txt);
script_name="insert_entries-${start_date}-${end_date}.sh"
toggl_url='https://www.toggl.com/api/v8/time_entries?user_agent=matt@alxndr.me&workspace_id=15478'
toggl_url+="&start_date=${start_date}T15%3A42%3A46%2B02%3A00"
toggl_url+="&&end_date=${end_date}T15%3A42%3A46%2B02%3A00"

# Run script
code=$(curl -s -u "$key:api_token" -X GET "$toggl_url" | php toggle_entries.php)

# Save to file
echo "$code" > "$script_name"

# Output code to terminal
echo = TIME ENTRY UPLOAD SCRIPT START ============================
echo "$code"
echo = TIME ENTRY UPLOAD SCRIPT END ==============================

# Message to user
echo "Script saved to $script_name"

# Upload to Jira
echo "Press any key to submit the time to Jira (or Crtl+C to exit) ..."
read -n 1
echo "Submitting ..."
. "$script_name"
