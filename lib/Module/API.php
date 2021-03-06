<?php
/**
 * Wraps all the Routing for the API Module
 */

namespace App\Module;

class API extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', function($REQ, $RES) {
			$data = array('Page' => array('title' => 'API'));
			return $this->view->render($RES, 'page/api/index.html', $data);
		})->add('App\Middleware\Menu');

		$a->group('/v2015', function() {

			$this->get('/metric', function($REQ, $RES, $ARG) {

				$ret = array();
				$res = $this->DB->fetchAll('SELECT * FROM lab_metric ORDER BY type, name');
				foreach ($res as $rec) {
					$rec['meta'] = json_decode($rec['meta'], true);
					$ret[ $rec['id'] ] = array(
						'id' => $rec['id'],
						'type' => $rec['type'],
						'name' => $rec['name'],
						'uom' => $rec['meta']['uom'],
					);
				}

				return $RES->withJSON($ret, 200, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

			});

			// Return List of Samples
			// $this->get('/qa', function($REQ, $RES, $ARG) {
			// 	return require_once(APP_ROOT . '/api/qa/search.php');
			// })->add('App\Middleware\Auth');

			//$this->post('/qa', function($REQ, $RES, $ARG) {
			//	die('Create QA Sample');
			//})->add($MWA);

			//$this->get('/qa/sample', function($REQ, $RES, $ARG) {
			//	die('List QA Samples');
			//})->add($MWA);
			$this->get('/result/{id}.pdf', function($REQ, $RES, $ARG) {
				return require_once(APP_ROOT . '/api/qa/result.pdf.php');
			});

			// Create a Sample
			//$this->post('/qa/sample', function($REQ, $RES, $ARG) {
			//	require_once(APP_ROOT . '/api/qa/sample-create.php');
			//})->add('Middleware_Auth');

			// Select Specific QA Sample
			$this->get('/result/{id}', 'App\Controller\API\Result\View');

			// // Select QA Sample+Result
			// $this->get('/sample/{code}/result', function($REQ, $RES, $ARG) {
			// 	require_once(APP_ROOT . '/api/qa/sample.php');
			// });

			// Update QA Result
			//$this->post('/qa/{code}/result', function($REQ, $RES, $ARG) {
			//	require_once(APP_ROOT . '/api/qa/result.php');
			//})->add('Middleware_Auth');

		});
	}
}
