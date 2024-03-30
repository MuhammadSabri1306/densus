<?php

class Rtu_map_model extends CI_Model
{
    protected $tableRtuName = 'rtu_map';
    protected $tableNonRegionalName = 'master_non_regional'; // non => Newosase/New
    protected $tableNonWitelName = 'master_non_witel';

    protected $currUser = null;

    public function __construct() {
        $this->load->database('densus');
    }

    public function setCurrUser($currUser)
    {
        $this->currUser = $currUser;
    }

    private function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('witel_kode', $currUser['locationId']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('divre_kode', $currUser['locationId']);
        }
    }

    public function setFilter($filters = [])
    {
        if(count($filters) > 0) {
            $this->db->where($filters);
        }
    }

    public function get($id = null, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        $this->db
            ->select()
            ->from($this->tableRtuName);

        if(is_null($id)) {
            return $this->db
                ->order_by('divre_kode')
                ->order_by('witel_kode')
                ->order_by('sto_kode')
                ->get()
                ->result();
        }

        return $this->db
            ->where('id', $id)
            ->get()
            ->row();
    }

    public function get_newosase_rtus($divreCode = null, $witelCode = null)
    {
        if($this->currUser['level'] == 'regional') {
            $divreCode = $this->currUser['locationId'];
        } elseif($this->currUser['level'] == 'witel') {
            $witelCode = $this->currUser['locationId'];
        }

        $filters = [];
        $this->load->library('rest_client', [ 'baseUri' => 'https://newosase.telkom.co.id/api/v1' ] );
        $this->rest_client->request['query'] = [
            'isArea' => 'hide',
            'isChildren' => 'view'
        ];

        if($divreCode) {
            $filters['divre_kode'] = $divreCode;
            $regional = $this->db
                ->select('*')
                ->from($this->tableNonRegionalName)
                ->where('divre_kode', $divreCode)
                ->get()
                ->row_array();
            if(!$regional) throw new ModelEmptyDataException('Gagal terhubung dengan Newosase.');
            $this->rest_client->request['query']['regional'] = $regional['regional_id'];
        }

        if($witelCode) {
            $filters['witel_kode'] = $witelCode;
            $witel = $this->db
                ->select('*')
                ->from($this->tableNonWitelName)
                ->where('witel_kode', $witelCode)
                ->get()
                ->row_array();
            if(!$witel) throw new ModelEmptyDataException('Gagal terhubung dengan Newosase.');
            $this->rest_client->request['query']['witel'] = $witel['witel_id'];
        }

        $this->db
            ->select('*')
            ->from($this->tableRtuName)
            ->where($filters);
        $rtuMapData = $this->db->get()->result_array();
        $availableRtus = array_map(fn($item) => $item['rtu_kode'], $rtuMapData);

        $rtus = [];
        try {
            $osaseData = $this->rest_client->sendRequest('GET', 'https://newosase.telkom.co.id/api/v1/parameter-service/mapview');
            foreach($osaseData->result as $regionalItem) {
                foreach($regionalItem->witel as $witelItem) {
                    foreach($witelItem->rtu as $rtuItem) {
                        array_push($rtus, [
                            'rtu_kode' => $rtuItem->rtu_sname,
                            'rtu_name' => $rtuItem->rtu_name,
                            'is_available' => in_array($rtuItem->rtu_sname, $availableRtus)
                        ]);
                    }
                }
            }
        } catch(\GuzzleHttp\Exception\RequestException $err) {
            // dd( (string) $err );
            // dd( $err->getRequest() );
            // dd( $err->getRequest()->getUri() );
            throw new ModelEmptyDataException('Gagal terhubung dengan Newosase.');
        }

        return $rtus;

    }

    public function get_by_rtu_code($rtuCode, $currUser = null) {
        $this->filter_for_curr_user($currUser);
        $query = $this->db
            ->select()
            ->from($this->tableRtuName)
            ->where('id', $id)
            ->get();
        return $query->row();
    }

    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $isRtuDuplicate = $this->db
                ->select('rtu_kode')
                ->from($this->tableRtuName)
                ->where('rtu_kode', $body['rtu_kode'])
                ->get()
                ->num_rows() > 0;
            if($isRtuDuplicate) {
                throw new FormValidationException(
                    'Kode RTU sudah ada dan tidak dapat ditambahkan. Silahkan menggunakan fitur update RTU untuk melakukan perubahan.',
                    [ 'is_rtu_kode_duplicate' => true ]
                );
            }
            $success = $this->db->insert($this->tableRtuName, $body);
        } else {
            $this->filter_for_curr_user($currUser);
            $this->db->where('id', $id);

            $success = $this->db->update($this->tableRtuName, $body);
        }
        return $success;
    }

    public function delete($id, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        $this->db->where('id', $id);
        return $this->db->delete($this->tableRtuName);
    }

    public function getByPue($filter, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('witel_kode', $filter['witel']);

        $query = $this->db
            ->select('*')
            ->from($this->tableRtuName)
            ->where('port_pue!=""')
            ->get();
        return $query->result();
    }

}