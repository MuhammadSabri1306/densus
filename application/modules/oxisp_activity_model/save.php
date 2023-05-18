<?php

if(is_null($id)) {
    
    $success = $this->db->insert($this->tableName, $body);

} else {
    
    $filterLoc = $this->get_loc_filter([]);
    $this->db
        ->where('id', $id)
        ->where($filterLoc);
    $success = $this->db->update($this->tableName, $body);

}
$this->result = $success;