<?php

require_once('db.php');

class Countries {
  private $db;

  function __construct() {
    $db = new DB();
    $this->db = $db;
    if (!$this->db->connected) {
      $this->db->connect();
    }
  }
}
