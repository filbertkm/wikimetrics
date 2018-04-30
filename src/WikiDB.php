<?php

namespace MetricsTool;

use PDO;

class WikiDB {

	/**
	 * @var PDO
	 */
    private $db;

    public function __construct()
    {
        $this->connectToDb();
    }

    public function getEditCount($users)
    {
	    $startDate = '201701';

        $placeholders = str_repeat ('?, ',  count ($users) - 1) . '?';

        $sql = 'SELECT rev_user_text, substring(rev_timestamp, 1, 6) as monthdate, count(*) as count '
	            . ' FROM revision_userindex '
				. " WHERE rev_user_text IN ($placeholders) "
	            . ' AND rev_timestamp > ' . $startDate . '01000000 '
	            . ' GROUP BY rev_user_text, monthdate '
                . ' ORDER BY rev_user_text, monthdate';

        $q = $this->db->prepare($sql);
        $q->execute($users);

        $results = $q->fetchAll();

        foreach ($results as $result) {
            $userName = $result['rev_user_text'];
            $monthDate = $result['monthdate'];
            $editCounts[$userName][$monthDate] = (int)$result['count'];
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
