<?php
/**
 * Show Result List
 */

namespace App\Controller;

use Edoceo\Radix\DB\SQL;

class Result extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = array(
			'Page' => array('title' => 'Results'),
			'sync' => false,
			'result_list' => array(),
		);

		$sql = <<<SQL
SELECT qa_result.*
FROM qa_result
JOIN qa_result_company ON qa_result.id = qa_result_company.lab_result_id
WHERE qa_result_company.company_id = :c
ORDER BY created_at DESC, qa_result.id
SQL;

		$arg = array(':c' => $_SESSION['gid']);
		$res = SQL::fetch_all($sql, $arg);
		foreach ($res as $rec) {

			$QAR = new QA_Result($rec);

			$rec['meta'] = \json_decode($rec['meta'], true);
			$rec['meta_result_cre'] = \json_decode($rec['meta_result_cre'], true);

			$rec['coa_file'] = $QAR->getCOAFile();

			// Try to Read first from META -- our preferred data, then try meta_result_cre
			$rec['created_at'] = _date('m/d/y', $rec['created_at']);
			$rec['thc'] = $rec['meta']['Result']['thc'] ?: '?';
			$rec['cbd'] = $rec['meta']['Result']['cbd'] ?: '?'; //  $rec['meta_result_cre']['thc'];
			$rec['sum'] = $rec['meta']['Result']['sum'] ?: '?';
			$rec['testing_status'] = $rec['meta']['Result']['testing_status'] ?: $rec['meta_result_cre']['testing_status'];
			$rec['status'] = $rec['meta']['Result']['status'] ?: $rec['meta_result_cre']['status'];

			$t = array();
			$x = $rec['meta']['Result']['batch_type'];
			if (empty($x)) {
				$x = $rec['meta_result_cre']['batch_type'];
			}
			$t[] = $x;

			$x = $rec['meta']['Result']['type'];
			if (empty($x)) {
				$x = $rec['meta_result_cre']['type'];
			}
			$t[] = $x;

			$x = $rec['meta']['Result']['intermediate_type'];
			if (empty($x)) {
				$x = $rec['meta_result_cre']['intermediate_type'];
			}
			$t[] = $x;
			$rec['type'] = trim(implode('/', $t), '/');
			$rec['type_nice'] = $rec['meta']['Product']['type_nice'];
			if (empty($rec['type_nice'])) {
				$rec['type_nice'] = $rec['meta']['Result']['type_nice'];
			}
			if (empty($rec['type_nice'])) {
				$rec['type_nice'] = $rec['type'];
			}

			$stat = array();
			if (!empty($rec['coa_file'])) {
				if (is_file($rec['coa_file'])) {
					$stat[] = ' <i class="far fa-file-pdf"></i>';
				} else {
					$stat[] = ' <i class="far fa-file-pdf text-danger"></i>';
				}
			}

			$x = sprintf('%s/%s', $rec['testing_status'], $rec['status']);
			switch ($x) {
			case 'completed/failed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--red);"></i>';
				break;
			case 'completed/passed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--green);"></i>';
				break;
			case 'in_progress/passed':
				$stat[] = '<i class="fas fa-clock"></i> <i class="fas fa-check-square" style="color: var(--green);"></i>';
				break;
			default:
				$stat[] = h($x);
			}

			$rec['status_html'] = implode(' ', $stat);

			$rec['flag_sync'] = ($rec['flag'] & \App\QA_Result::FLAG_SYNC);
			if ($rec['flag_sync']) {
				$data['sync'] = true;
			}

			$data['result_list'][] = $rec;

		}

		return $this->_container->view->render($RES, 'page/result/index.html', $data);

	}
}