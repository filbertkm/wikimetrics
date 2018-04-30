<?php

namespace MetricsTool;

use PDO;

class WikiDB {

    private $db;

    public function __construct()
    {
        $this->connectToDb();
    }

    public function getEditCount($users)
    {
        $editCounts = [];

        $year = 2017;
        $month = 7;

        while ($year < 2019) {
            $date = $year . '-' . $month;

            $stats = $this->getEditCountsForMonth($year, $month, $users);
            $editCounts[$date] = $stats;

            if ($month === 12) {
                $year++;
                $month = 1;
            } else {
                $month++;
            }

            if ($year === 2018 && $month === 5) {
                break;
            }
        }

        return $editCounts;
    }

    private function getEditCountsForMonth($year, $month, $users)
    {
        $nextYear = $month === 12 ? $year + 1 : $year;
        $nextMonth = $month === 12 ? 1 : $month + 1;

        $month = $month >= 10 ? (string)$month : "0$month";
        $nextMonth = $month >= 10 ? (string)$nextMonth : "0$nextMonth";

        $placeholders = str_repeat ('?, ',  count ($users) - 1) . '?';

        $sql = 'SELECT rev_user_text, count(*) as count '
            . ' FROM revision_userindex '
            . " WHERE rev_user_text IN ($placeholders) "
            . ' AND rev_timestamp >= ' . $year . $month . '01000000 '
            . ' AND rev_timestamp < ' . $nextYear . $nextMonth . '01000000 '
            . ' GROUP BY rev_user_text ';

        $q = $this->db->prepare($sql);
        $q->execute($users);

        $results = $q->fetchAll();

        $editCounts = array_combine($users, array_map(function($user) { return 0; }, $users));

        foreach ($results as $result) {
            $userName = $result['rev_user_text'];
            $editCounts[$userName] = (int)$result['count'];
        }

        ksort($editCounts);

        return $editCounts;
    }

    private function connectToDb($wiki = 'enwiki')
    {
        $ts_pw = posix_getpwuid(posix_getuid());
        $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

        $this->db = new PDO(
            "mysql:host=enwiki.analytics.db.svc.eqiad.wmflabs;dbname=enwiki_p",
            $ts_mycnf['user'],
            $ts_mycnf['password']
        );

        unset($ts_mycnf, $ts_pw);
    }

}
