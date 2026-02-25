<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Page extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		// $this->load->library('form_validation');
		// $this->load->library('session');
		// $this->load->model('userappsmodel');
		$this->load->model('page_model', 'pagemodel');
		// $this->load->model('helper_model', 'helpermodel');
		// if(get_cookie('bantuMasukMomois')=='YES'){
		//     $this->_loginwithcookie(get_cookie('usernameMasukMomois'),get_cookie('passwordMasukMomois'));
		// }
	}
	public function fn()
	{
		$footer['halaman'] = 'fn';
		$this->session->set_userdata('sesifn', time());
		$this->load->view('layouts/header');
		$this->load->view('infin.php');
		$this->load->view('layouts/footer', $footer);
	}
	public function gf()
	{
		$footer['halaman'] = 'gf';
		$this->session->set_userdata('sesigf', time());
		$this->load->view('layouts/header');
		$this->load->view('ingf.php');
		$this->load->view('layouts/footer', $footer);
	}
	public function boxe()
	{
		$footer['halaman'] = 'bx';
		$this->session->set_userdata('sesibx', time());
		$data['pl_list'] = $this->pagemodel->getplno();
		$plno = $this->input->post('selectedPlNo', true);
		// if ($plno === null && !empty($data['pl_list'])) {
		// 	$plno = $data['pl_list'][0]['plno'];
		// }
		$data['plno'] = $plno;           // so view can use $plno
		$data['selectedPlNo'] = $plno;

		$this->load->view('layouts/header');
		$this->load->view('inbox.php',$data);
		$this->load->view('layouts/footer', $footer);
	}
	public function box()
	{
		$footer['halaman'] = 'bx';
		$data['pl_list'] = $this->pagemodel->getplno();
		$plno = $this->input->post('selectedPlNo', true);
		if ($plno === null && !empty($data['pl_list'])) {
			$plno = $data['pl_list'][0]['plno'];
		}
		$data['plno'] = $plno;           // so view can use $plno
		$data['selectedPlNo'] = $plno;

		$showDone = $this->input->post('show_done', true) === '1';

		if ($showDone) {
			$data['orders'] = $this->pagemodel->getOrderByPlNoDone($plno);
		} else {
			$data['orders'] = $this->pagemodel->getOrderByPlNo($plno);
		}

		//progress bar 
		$allOrders = $this->pagemodel->getOrderByPlNo($plno);
		$doneOrders = $this->pagemodel->getOrderByPlNoDone($plno);
		$totalCount = count($allOrders);
		$doneCount = count($doneOrders);
		$progressPerc = $totalCount ? round($doneCount / $totalCount * 100) : 0;



		// **Pass it to the view**
		$data['show_done'] = $showDone;
		$data['totalOrdersCount'] = $totalCount;
		$data['doneOrdersCount'] = $doneCount;
		$data['progressPercent'] = $progressPerc;


		$this->load->view('layouts/header');
		$this->load->view('outbox.php', $data);
		$this->load->view('layouts/footer', $footer);
	}
	public function validateHandheld()
	{
		$data = $_POST['data'];
		$x = 0;
		foreach ($data as $dat => $value) {
			$hasil = $this->pagemodel->cekdatafn($value['value']);
			if (count($hasil) > 0) {
				$data[$x]['value'] = $hasil['isi'];
				$data[$x]['status'] = $hasil['status'];
				$data[$x]['done'] = $hasil['done'];

			} else {
				$data[$x]['value'] = $value['value'] . ' - (NOT FOUND)';
				$data[$x]['status'] = 'NG';
				$data[$x]['done'] = 'NOT FOUND';
			}
			$x++;
		}
		echo json_encode($data);
	}
	public function validateingudang()
	{
		$data = $_POST['data'];
		$x = 0;
		foreach ($data as $dat => $value) {
			$hasil = $this->pagemodel->cekDataIn($value['value']);
			if (count($hasil) > 0) {
				$data[$x]['value'] = $hasil['isi'];
				$data[$x]['status'] = $hasil['status'];
				$data[$x]['done'] = $hasil['done'];
			} else {
				$data[$x]['value'] = $value['value'] . ' - (NOT FOUND)';
				$data[$x]['status'] = 'NG';
				$data[$x]['done'] = 'TIDAK DI GUDANG';
			}
			$x++;
		}
		echo json_encode($data);
	}
	public function validateinbox()
	{
		$data = $_POST['data'];
		$pl = $_POST['plno'];
		$x = 0;
		foreach ($data as $dat => $value) {
			$hasil = $this->pagemodel->cekDataInbox($value['value'],$pl);
			if ($hasil['kondisi']=='sukses') {
				$data[$x]['value'] = $hasil['isi'];
				$data[$x]['status'] = $hasil['status'];
				$data[$x]['done'] = $hasil['done'];
			} else {
				$data[$x]['value'] = $value['value'] . ' - (NOT FOUND)';
				$data[$x]['status'] = 'NG';
				$data[$x]['done'] = $hasil['done'];
			}
			$x++;
		}
		echo json_encode($data);
	}

	public function validateContainer()
	{
		$plno = $this->input->post('selectedPlNo', true);
		$scans = $this->input->post('data', true);
		$out = [];

		foreach ($scans as $scan => $s) {
			$epc = $s['value'];

			$res = $this->pagemodel->checkRFIDForBale($epc, $plno);

			if (is_array($res) && isset($res['isi'], $res['status'])) {
				$out[$scan] = [
					'key' => $scan + 1,
					'value' => $res['isi'],
					'status' => $res['status'],
					'done' => '',            // you can choose to include a done-message
				];
			} else {
				// otherwise assume $res is a string message and show NG
				$out[$scan] = [
					'key' => $scan + 1,
					'value' => $epc,
					'status' => 'NG',
					'done' => is_string($res) ? $res : 'NG',
				];
			}
		}
		echo json_encode(array_values($out));
	}
}
