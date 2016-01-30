<?php

class Config {
  private $settings_file = 'settings.ini';
  public $settings = null;

  function __construct() {
    $this->settings = parse_ini_file($this->settings_file);
  }
}

$config = new Config();
