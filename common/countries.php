<?php

require_once('db.php');

class Countries {
  private $db;

  function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->connected) {
      $this->db->connect();
    }
  }
}
