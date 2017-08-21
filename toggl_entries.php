<?php

/**
 * Output CSV-formatted output from Toggl that can be entered into Jira
 */
class TogglCaller
{

    public static function getPropertyValues()
    {
        $configurationFile = getenv('PROP_FILE');
        $result = array();
        $lines = file($configurationFile);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $pos = strpos($line, '=');

            if ($pos === false) {
                throw new RuntimeException(sprintf('Invalid config line "%s" (no separator)', $line));
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            if (empty($key)) {
                throw new RuntimeException(sprintf('Invalid config line "%s" (empty key)', $line));
            }

            if (empty($value)) {
                throw new RuntimeException(sprintf('Config value "%s" cannot be empty', $key));
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * TODO: Make this method longer
     *
     */
    public static function call($in)
    {
        $configuration = self::getPropertyValues();
        extract($configuration);

        if (empty($in)) {
            throw new InvalidArgumentException("Invalid argument value");
        }

        if (empty($JIRA_URL)) {
            throw new RuntimeException("Missing config \"JIRA_URL\"");
        }
        if (empty($JIRA_USER)) {
            throw new RuntimeException("Missing config \"JIRA_USER\"");
        }
        if (empty($JIRA_PASSWORD_FILE)) {
            throw new RuntimeException("Missing config \"JIRA_PASSWORD_FILE\"");
        }
        if (empty($TIMEZONE)) {
            throw new RuntimeException("Missing config \"TIMEZONE\"");
        }

        date_default_timezone_set($TIMEZONE);

        // Get time entries from JSON
        $entries = json_decode($in);

        if (!is_array($entries)) {
            throw new RuntimeException(sprintf('Toggl server returned error: "%s"', trim($in)));
        }
        if (count($entries) === 0) {
            throw new RuntimeException('No entries in that time period');
        }

        // Build array of data
        $starts = array();
        $projects = array();

        // Build projects array
        if (!empty($configuration['PROJECTS'])) {
            $projects = explode(',', $configuration['PROJECTS']);
        }

        foreach ($entries as $entry) {

            // Description should be in Jira ticket ID format: "MYPROJ-123"
            if (preg_match('/^([A-Z0-9]+-\d+)\b/', $entry->description, $matches) === 0) {
                continue;
            }

            // Currently running durations are negative
            if ($entry->duration <= 0) {
                continue;
            }

            // extract project id from description:
            if (!empty($projects)) {
                preg_match('/^([A-Z0-9]+)\b/', $entry->description, $project);
                $project = $project[0];
                if (!in_array($project, $projects)) {
                    continue;
                }
            }

            // From https://github.com/toggl/toggl_api_docs
            // Times and dates use the ISO 8601 standard, more specifically a subset described in RFC 3339.
            $start = date(DATE_RFC3339, strtotime($entry->start));
            $ticket = $matches[1];

            if (!isset($starts[$start][$ticket])) {
                $starts[$start][$ticket] = 0;
            }

            $starts[$start][$ticket] += $entry->duration;
        }

        // Aggregate data
        $rows = array();
        foreach ($starts as $start => $tickets) {
            foreach ($tickets as $ticket => $seconds) {
                $minutes = $seconds / 60;
                $hours = $minutes / 60;

                // Skip tasks less than 5 minutes
                if ($minutes < 5) {
                    continue;
                }

                $spent = sprintf('%.2fh', $hours);

                $rows[] = compact('ticket', 'start', 'spent');
            }
        }

        // Sort data
        $tickets = array();
        $starts = array();
        foreach ($rows as $key => $row) {
            $tickets[$key] = $row['ticket'];
            $starts[$key] = $row['start'];
        }
        array_multisort($tickets, SORT_ASC, $starts, SORT_ASC, $rows);

        // Script
        $url = $JIRA_URL . '/rest/api/2';
        ob_start();
        ?>#!/bin/bash
        <?php foreach ($rows as $row): ?>

        <?php
        $entry = new StdClass();

// Jira uses a custom format similar to ISO 8601 RFC 3339
// https://answers.atlassian.com/questions/180275/update-jira-rest-api-datetime-value
        $jira_format = 'Y-m-d\TH:i:s.000O';
        $start_datetime = new DateTime($row['start']);
        $jira_datetime = $start_datetime->format($jira_format);

        $entry->started = $jira_datetime;
        $entry->timeSpent = $row['spent'];
        $entry->author = new StdClass;
        $entry->author->self = $url . '/user?username=' . $JIRA_USER;
        ?>
        echo '<?php echo "{$row['ticket']} {$entry->started} $entry->timeSpent;"; ?>'
        curl -u <?php echo $JIRA_USER ?>:$(cat <?php echo $JIRA_PASSWORD_FILE ?>) -X POST -H "Content-Type: application/json" \
        --data '<?php echo json_encode($entry); ?>' \
        <?php echo $url; ?>/issue/<?php echo $row['ticket']; ?>/worklog
        echo ""
        echo ""
    <?php endforeach; ?>
        <?php
        return ob_get_clean() . "\n";
    }
}

// Get JSON from stdin
$fd = fopen('php://stdin', 'r');
$in = '';
while (!feof($fd)) {
    $in .= fread($fd, 1024);
}
fclose($fd);

try {
    $out = TogglCaller::call($in);

    // Write string to stdout
    $fd = fopen('php://stdout', 'w');
    fwrite($fd, $out);
} catch (Exception $e) {
    print $e->getMessage();
}
