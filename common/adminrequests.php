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
      'session_id'  => FILTER_VALIDATE_INT,
      'cookie'      => FILTER_SANITIZE_STRING,
      'name'        => FILTER_SANITIZE_STRING,
      'password'    => FILTER_UNSAFE_RAW,
      'password2'   => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[0-9a-f]{64}$/'
        ),
      ),
      'admin'       => FILTER_VALIDATE_INT,
      'status'      => FILTER_VALIDATE_INT,
      'visible'     => FILTER_VALIDATE_INT,
      'logo'        => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w-]+$/'
        ),
      ),
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
      'action'      => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w-]+$/'
        ),
      )
    );
    $this->actions = array(
      'create_team',
      'create_level',
      'update_team',
      'update_level',
      'delete_team',
      'delete_level',
      'delete_session',
      'toggle_status_level',
      'toggle_status_team',
      'toggle_admin_team',
      'toggle_visible_team'
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
