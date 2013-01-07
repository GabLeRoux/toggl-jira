<?php
/**
 * Output CSV-formatted output from Toggl that can be entered into Jira
 *
 * Usage:
 *
 * toggl_user='[INSERTKEY]:api_token'
 * curl -u $toggl_user -X GET "https://www.toggl.com/api/v6/time_entries.json?start_date=2012-10-07T00%3A00%3A01-05%3A00&end_date=2012-10-20T23%3A59%3A59-05%3A00" \
 * | php toggle_entries.php | sort
 *
 * Output a date for the URL:
 * php -r "var_dump(urlencode(date('c', strtotime('2012-10-07 00:00:01'))));"
 */

// Get JSON from stdin
$fd = fopen('php://stdin', 'r');
$in = '';
while (!feof($fd)) {
    $in .= fread($fd, 1024);
}
fclose($fd);

// Get time entries from JSON
$in = json_decode($in);
$entries = $in->data;

// Build array of data
$days = array();
foreach ($entries as $entry) {
    
    // Description should be in Jira ticket ID format: "MYPROJ-123"
    if (preg_match('/^([A-Z0-9]+-\d+)$/', $entry->description, $matches) === 0) {
        continue;
    }

    // Currently running durations are negative
    if ($entry->duration <= 0) {
        continue;
    }

    $day = date('j/M/y', strtotime($entry->start)).' 12:01 AM';
    $ticket = $matches[1];

    if (!isset($days[$day][$ticket])) {
        $days[$day][$ticket] = 0;
    }

    $days[$day][$ticket] += $entry->duration;
}

// Aggregate data
$rows = array();
foreach ($days as $day => $tickets) {
    foreach ($tickets as $ticket => $duration) {

        $hours = $duration / 3600;
        $spent = sprintf('%.2fh', $hours);

        if ($spent === '0.00') {
            continue;
        }

        $rows[] = array($ticket, $day, $spent);
    }
}

// Get CSV string
ob_start();
$fp = fopen('php://output', 'w');
foreach ($rows as $row) {
    fputcsv($fp, $row);
}
fclose($fp);
$out = ob_get_clean();

// Write string to stdout
$fd = fopen('php://stdout', 'w');
fwrite($fd, $out);
