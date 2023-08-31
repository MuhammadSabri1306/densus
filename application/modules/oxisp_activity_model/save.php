<?php

if(is_null($id)) {
    
    $success = $this->db->insert($this->tableName, $body);

} else {
    
    $this->db->where('id', $id);
    $success = $this->db->update($this->tableName, $body);

}
$this->result = $success;