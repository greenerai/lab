<?php
/**
 * Details about Sharing
*/

namespace App\Controller;

use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

class Search extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/search.html';
		$data = array(
			'Page' => array('title' => 'Search'),
			'result_list' => [],
		);

		$q = trim($_GET['q']);
		if (!empty($q)) {

			// Search Specific Tables
			$sql = 'SELECT * FROM lab_sample WHERE id = ?';
			$arg = array($q);
			$res = $this->_container->DB->fetchRow($sql, $arg);
			if (!empty($res['id'])) {
				return $RES->withRedirect('/result/' . $q);
			}

			$sql = 'SELECT * FROM lab_result WHERE id = ?';
			$arg = array($q);
			$res = $this->_container->DB->fetchRow($sql, $arg);
			if (!empty($res['id'])) {
				return $RES->withRedirect('/result/' . $q);
			}

			$sql = <<<SQL
SELECT id, 'lab_sample' AS type FROM lab_sample WHERE id LIKE :q0
UNION ALL
SELECT id, 'lab_result' AS type FROM lab_result WHERE id LIKE :q0
-- UNION ALL
-- SELECT id, 'transfer' FROM transfer WHERE id LIKE :q0
LIMIT 10
SQL;

			$arg = [
				':q0' => sprintf('%%%s%%', $q)
			];

			$res = $this->_container->DB->fetchAll($sql, $arg);
			foreach($res as $r) {
				switch ($r['type']) {
				case 'lab_sample':
					$r['link'] = sprintf('/sample/%s', $r['id']);
					break;
				case 'lab_result':
					$r['link'] = sprintf('/result/%s', $r['id']);
					break;
				}

				$data['result_list'][] = $r;
			}

			// $sql = 'SELECT * FROM search WHERE ftsv @@ ??';
			// $arg = [plain_to_query(fs)]

		}

		return $this->_container->view->render($RES, $file, $data);

	}
}
