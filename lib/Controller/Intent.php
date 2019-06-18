<?php
/**
 * When Someone Has Intent
 *
 * This file is part of OpenTHC Laboratory Portal
 *
 * OpenTHC Laboratory Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published by
 * the Free Software Foundation.
 *
 * OpenTHC Laboratory Portal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenTHC Laboratory Portal.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Controller;

class Intent extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		if (!empty($_GET['_'])) {

			$arg = _decrypt($_GET['_']);
			$arg = json_decode($arg, true);

			if (!empty($arg['x'])) {
				// Expire Time
			}

			// Action
			switch ($arg['a']) {
			case 'coa-upload':

				$QAR = new \App\QA_Result($arg['r']);
				//var_dump($QAR); exit;

				switch ($_POST['a']) {
				case 'coa-upload':

					$coa_file = sprintf('%s/var/%06d/%s.pdf', APP_ROOT, $QAR['company_id'], $QAR['guid']);
					if (is_file($coa_file)) {
						_exit_text('Cannot Upload, Contact Support [LCI#033]', 409);
					}

					$coa_path = dirname($coa_file);
					if (!is_dir($coa_path)) {
						mkdir($coa_path, 0755, true);
					}
					\move_uploaded_file($_FILES['file']['tmp_name'], $coa_file);

					// Evaluate PDF
					$cmd = array();
					$cmd[] = '/usr/bin/pdftk';
					$cmd[] = escapeshellarg($coa_file);
					$cmd[] = 'dump_data';
					$buf = shell_exec(implode(' ', $cmd));

					// PageMediaRect: 0 0 612 792
					// PageMediaDimensions: 612 792
					if (preg_match('//')) {
					}

				}

				$file = 'page/intent/coa-upload.html';
				$data = array(
					'Page' => array('title' => 'COA Upload'),
					'Result' => array(
						'id' => $QAR['guid'],
					)
				);

				return $this->_container->view->render($RES, $file, $data);

				break;
			}
		}


		switch ($_SESSION['intent']) {
		case 'share-all':
			unset($_SESSION['intent']);
			unset($_SESSION['intent-data']);
			$RES = $RES->withRedirect('/result');
			break;
		case 'share-one':
			$RES = $RES->withRedirect('/result/' . $_SESSION['intent-data']);
			unset($_SESSION['intent']);
			unset($_SESSION['intent-data']);
			break;
		}

		return $RES;
	}
}