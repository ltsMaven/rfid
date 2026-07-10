<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Page_model extends CI_Model
{
    public function cekdatafn($val)
    {
        $id = 0;
        $sudahada = 0;
        $hasil = [];
        $this->db->where('rfid', $val);
        $cek = $this->db->get('tb_packfin');
        if ($cek->num_rows() > 0) {
            $data = $cek->row_array();
            $datarf = $this->db->get_where('tb_rfid', ['rfid' => $data['rfid']]);
            if ($datarf->num_rows() > 0) {
                $cekdatarf = $datarf->row_array();
                $id = $cekdatarf['id'];
                if ($cekdatarf['sesifn'] != $this->session->userdata('sesifn')) {
                    $sudahada = 1;
                }
            } else {
                $currentDateTime = new DateTime('now');
                $field = [
                    'po' => $data['po'],
                    'item' => $data['item'],
                    'dis' => $data['dis'],
                    'nobale' => $data['nobale'],
                    'rfid' => $val,
                    'gate_out' => $currentDateTime->format('Y-m-d H:i:s'),
                    'sesifn' => $this->session->userdata('sesifn'),
                ];
                $this->db->insert('tb_rfid', $field);
                $id = $this->db->insert_id();
            }
        }
        if ($id > 0) {
            $xhas = $this->db->get_where('tb_rfid', ['id' => $id])->row_array();
            $dis = $xhas['dis'] == 0 ? '' : ' dis ' . $xhas['dis'];
            $adarf = $sudahada == 1 ? ' SUDAH ADA PADA DATA OUT ' : 'BERHASIL INPUT';
            $nobale = ' Bale No. ' . $xhas['nobale'];
            $hasil['isi'] = $xhas['po'] . '#' . trim($xhas['item']) . $dis . $nobale . ' (' . $xhas['gate_out'] . ')';
            $hasil['done'] = $adarf;
            $hasil['status'] = $sudahada == 1 ? 'SA' : 'OK';
        } else {
            $hasil = [];
        }
        return $hasil;
    }

    public function cekDataIn($val)
    {
        $hasil = [];
        $id = 0;
        $sudahada = 0;
        $this->db->where('rfid', $val);
        $cek = $this->db->get('tb_rfid');
        if ($cek->num_rows() > 0) {
            $datacek = $cek->row_array();
            if ($datacek['sesi_gf'] != null & $datacek['gate_in'] != null) {
                $sudahada = 1;
                $id = $datacek['id'];
            } else {
                $currentDateTime = new DateTime('now');
                $data = [
                    'sesi_gf' => $this->session->userdata('sesigf'),
                    'gate_in' => $currentDateTime->format('Y-m-d H:i:s'),
                ];
                $this->db->where('id', $datacek['id']);
                $this->db->update('tb_rfid', $data);
                $id = $datacek['id'];
            }
        }
        if ($id > 0) {
            $xhas = $this->db->get_where('tb_rfid', ['id' => $id])->row_array();
            $dis = $xhas['dis'] == 0 ? '' : ' dis ' . $xhas['dis'];
            $adarf = $sudahada == 1 ? ' SUDAH DI INPUT ' : 'BERHASIL MASUK';
            $nobale = ' Bale No. ' . $xhas['nobale'];
            $hasil['isi'] = $xhas['po'] . '#' . trim($xhas['item']) . $dis . $nobale . ' (' . $xhas['gate_in'] . ')';
            $hasil['done'] = $adarf;
            $hasil['status'] = $sudahada == 1 ? 'SA' : 'OK';
        } else {
            $hasil = [];
        }
        return $hasil;
    }

    public function cekDataInbox($val,$pl)
    {
        $hasil = [];
        $id = 0;
        $sudahada = 0;
        // Cek data IN masuk ke Gudang 
        $this->db->where('rfid',$val);
        $cekrfid = $this->db->get('tb_rfid');
        if($cekrfid->num_rows() > 0){
            $hasilrfid = $cekrfid->row_array();
            // Cek data Register packfin 
            $this->db->where('rfid', $val);
            $cekpackfin = $this->db->get('tb_packfin');
            if ($cekpackfin->num_rows() > 0) {
                $hasilpackfin = $cekpackfin->row_array();
                $this->db->where('po',$hasilpackfin['po']);
                $this->db->where('item',$hasilpackfin['item']);
                // $this->db->where('dis',$hasilpackfin['dis']);
                $this->db->where('nobale',$hasilpackfin['nobale']);
                $this->db->where('plno',$pl);
                $cekbalenumber = $this->db->get('tb_balenumber');
                if($cekbalenumber->num_rows() > 0){
                    $balenumber = $cekbalenumber->row_array();
                    if($balenumber['masuk'] >= 1){
                        if($balenumber['selesai'] == 1){
                            $sudahada = 1;
                            $id = $balenumber['id'];
                        }else{
                            $currentDateTime = new DateTime('now');
                            
                            $this->db->where('id',$hasilrfid['id']);
                            $this->db->update('tb_rfid',['cont_in' => $currentDateTime->format('Y-m-d H:i:s')]);

                            $this->db->where('id', $balenumber['id']);
                            $this->db->update('tb_balenumber', ['selesai' => 1, 'waktu_selesai' => $currentDateTime->format('Y-m-d H:i:s')]);
                            $id = $balenumber['id'];
                        }
                    }else{
                        $dis = $hasilpackfin['dis'] == 0 ? '' : ' dis ' . $hasilpackfin['dis'];
                        $nobale = ' Bale No. ' . $hasilpackfin['nobale'];
                        $isi = $hasilpackfin['po'] . '#' . trim($hasilpackfin['item']) . $dis . $nobale . ' - ( NOT FOUND)';
                        $done = 'BALE BELUM DI CEK';
                        $status = 'NG';
                    }
                }else{
                    $dis = $hasilpackfin['dis'] == 0 ? '' : ' dis ' . $hasilpackfin['dis'];
                    $nobale = ' Bale No. ' . $hasilpackfin['nobale'];
                    $isi = $hasilpackfin['po'] . '#' . trim($hasilpackfin['item']) . $dis . $nobale . ' - ( NOT FOUND)';
                    $done = 'TIDAK ADA DI PACKING LIST';
                    $status = 'NG';
                }
            }else{
                $isi = $val. ' - ( NOT FOUND)';
                $done = 'BLM KELUAR FN';
                $status = 'NG';
            }
        }else{
            $isi = $val. ' - ( NOT FOUND)';
            $done = 'RFID TIDAK DITEMUKAN';
            $status = 'NG';
        }
        if ($id > 0) {
            $xhas = $this->db->get_where('tb_balenumber', ['id' => $id])->row_array();
            $dis = $xhas['dis'] == 0 ? '' : ' dis ' . $xhas['dis'];
            $adarf = $sudahada == 1 ? ' SUDAH DI INPUT ' : 'BERHASIL MASUK';
            $nobale = ' Bale No. ' . $xhas['nobale'];
            $hasil['kondisi'] = 'sukses';
            $hasil['isi'] = $xhas['po'] . '#' . trim($xhas['item']) . $dis . $nobale . ' (' . $xhas['waktu_selesai'] . ')';
            $hasil['done'] = $adarf;
            $hasil['status'] = $sudahada == 1 ? 'SA' : 'OK';
        } else {
            // $hasil = [];
            $hasil['kondisi'] = 'gagal';
            $hasil['isi'] = $isi;
            $hasil['done'] = $done;
            $hasil['status'] = $status;
        }
        return $hasil;
    }
    public function getPlNo()
    {
        return $this->db
            ->distinct()
            ->select('plno')
            ->from('tb_balenumber')
            // ->where('visible', 1)
            ->group_by('plno')          // one row per PL-No
            ->order_by('plno', 'ASC')
            ->get()
            ->result_array();
    }


    public function setPlVisible(string $plno, bool $visible = false): void
    {
        if ($this->countPending($plno) === 0) {
            // safe to hide
            $this->db
                ->where('plno', $plno)
                ->update('tb_balenumber', ['visible' => 0]);
        }

    }


    public function countPending(string $plno): int
    {
        return (int) $this->db
            ->where('plno', $plno)
            ->where('selesai', 0)
            ->count_all_results('tb_balenumber');
    }
    public function getOrderByPlNo(string $plno): array
    {

        if ($plno == '') {
            return [];
        }
        return $this->db
            ->select('id, po, item, dis, nobale, masuk, selesai')
            ->from('tb_balenumber')
            ->where('plno', $plno)
            // ->where('selesai', 0)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function getOrderByPlNoDone(string $plno): array
    {

        if ($plno == '') {
            return [];
        }
        return $this->db
            ->select('id, po, item, dis, nobale, masuk')
            ->from('tb_balenumber')
            ->where('plno', $plno)
            ->where('selesai', 1)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function checkRFIDForBale(string $epc, string $plno): array
    {
        //mengecheck kalo misalnya ada di tabel rfid atw di gudang 
        $rfidData = $this->db
            ->where('rfid', $epc)
            ->get('tb_rfid')
            ->row_array();


        if (!$rfidData) {
            return [
                'isi' => $epc,
                'status' => 'SA',
                'done' => 'BELUM DI GUDANG'
            ];
        }

        $exists = $this->db
            ->where('plno', $plno)
            ->where('po', $rfidData['po'])
            ->where('item', $rfidData['item'])
            ->where('dis', $rfidData['dis'])
            ->where('nobale', $rfidData['nobale'])
            ->get('tb_balenumber')
            ->row_array();

        if (!$exists) {
            return [
                'isi' => "{$rfidData['po']}/{$rfidData['item']} Bale {$rfidData['nobale']}",
                'status' => 'NO',
                'done' => "TIDAK UNTUK PL {$plno}"
            ];
        }

        $this->db->where('po', $rfidData['po'])
            ->where('item', $rfidData['item'])
            ->where('dis', $rfidData['dis'])
            ->where('nobale', $rfidData['nobale'])
            ->update('tb_balenumber', [
                'masuk' => date('H:i:s'),
                'selesai' => 1
            ]);

        $label = sprintf(
            "%d. %s / %s Bale %d",
            $exists['id'],
            $exists['po'],
            $exists['item'],
            $exists['nobale'],
        );

        return [
            'isi' => $label,
            'status' => 'OK'
        ];
    }
}


