<?php
/**
 * Output CSV-formatted output from Toggl that can be entered into Jira
 *
 */

/*
Usage:

toggl_user='[INSERTKEY]:api_token'
curl -u $toggl_user \
    -X GET "https://www.toggl.com/api/v8/time_entries?user_agent=matt@alxndr.me&workspace_id=15478&start_date=2013-12-27T15%3A42%3A46%2B02%3A00&end_date=2014-01-25T15%3A42%3A46%2B02%3A00" \
    | php toggle_entries.php > insert_entries.sh
. insert_entries.sh
*/

date_default_timezone_set('America/Chicago');

// Get JSON from stdin
$fd = fopen('php://stdin', 'r');
$in = '';
while (!feof($fd)) {
    $in .= fread($fd, 1024);
}
fclose($fd);

// Get time entries from JSON
$entries = json_decode($in);

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

    $day = date('Y-m-d', strtotime($entry->start));
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

        $rows[] = compact('ticket', 'day', 'spent');
    }
}

// Sort data
$tickets = array();
$days = array();
foreach ($rows as $key => $row) {
    $tickets[$key]  = $row['ticket'];
    $days[$key] = $row['day'];
}
array_multisort($tickets, SORT_ASC, $days, SORT_ASC, $rows);

// Script
ob_start();
?>#!/bin/bash
<?php foreach ($rows as $row): ?>

<?php
$entry = new StdClass();
$entry->started = date('Y-m-d\TH:i:s.000O', strtotime($row['day'].' 12:01 AM'));
$entry->timeSpent = $row['spent'];
$entry->author = new StdClass;
$entry->author->self = 'https://jira.careteamconnect.com/rest/api/2/user?username=alexandma';
?>
# <?php echo $row['ticket']; ?> <?php echo date('n/j/Y', strtotime($row['day'].' 12:01 AM')); ?> <?php echo $entry->timeSpent; ?>

curl -u AlexandMa:$(cat ~/.jira-pass) -X POST -H "Content-Type: application/json" \
    --data '<?php echo json_encode($entry); ?>' \
    https://jira.careteamconnect.com/rest/api/2/issue/<?php echo $row['ticket']; ?>/worklog
<?php endforeach; ?>
<?php
$out .= ob_get_clean();

// Write string to stdout
$fd = fopen('php://stdout', 'w');
fwrite($fd, $out);
?>
