<?php

class AdminRequests {

  private $input_method;
  private $method = '';
  private $filters = array();
  private $actions = array();

  public $action = 'none';
  public $parameters = array();

  function __construct() {
    $inputMethods = array('POST' => INPUT_POST);
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->input_method = $inputMethods[$this->method];
    $this->filters = array(
      'level_id'    => FILTER_VALIDATE_INT,
      'team_id'     => FILTER_VALIDATE_INT,
      'name'        => FILTER_UNSAFE_RAW,
      'password'    => FILTER_SANITIZE_STRING,
      'logo_id'     => FILTER_VALIDATE_INT,
      'entity_id'   => FILTER_VALIDATE_INT,
      'description' => FILTER_UNSAFE_RAW,
      'flag'        => FILTER_UNSAFE_RAW,
      'hint'        => FILTER_UNSAFE_RAW,
      'points'      => FILTER_VALIDATE_INT,
      'bonus'       => FILTER_VALIDATE_INT,
      'bonus_dec'   => FILTER_VALIDATE_INT,
      'bonus_fix'   => FILTER_VALIDATE_INT,
      'penalty'     => FILTER_VALIDATE_INT,
      'active'      => FILTER_VALIDATE_INT,
      'action'      => FILTER_SANITIZE_STRING,
    );
    $this->actions = array(
      'create_team',
      'create_level',
      'update_team',
      'update_level',
      'delete_team',
      'delete_level',
      'toggle_status_level',
      'toggle_status_team',
    );
  }

  public function processAdmin() {
    $this->parameters = filter_input_array($this->input_method, $this->filters);
    if ($this->parameters) {
      $this->action = $this->parameters['action'];
    }
    if (!in_array($this->action, $this->actions)) {
      $this->action = 'none';
    }
  }
}
