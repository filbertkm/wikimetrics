<?php

namespace MetricsTool;

class App {

    public function run()
    {
        $wikiDB = new WikiDB();

        $users = $this->getEditors();
        $stats = $wikiDB->getEditCount($users);

        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    private function getEditors($url = null)
    {
        $url = 'https://outreachdashboard.wmflabs.org/course_students_csv?course=AfroCROWD_and_the_Schomburg_Center/AfroCROWD_Schomburg_Center_Black_History_Month_Wikipedia_Edit-a-thon,_2018';

        $contents = file_get_contents($url);

        $lines = explode(PHP_EOL, $contents);

        $users = [];

        foreach ($lines as $k => $line) {
            if ($k !== 0 && !empty($line)) {
                $parts = explode(',', $line);
                $users[] = $parts[0];
            }
        }

        return $users;
    }

}
