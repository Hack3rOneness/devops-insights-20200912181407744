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
      'level_type'  => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[a-z]{4}$/'
        ),
      ),
      'team_id'     => FILTER_VALIDATE_INT,
      'session_id'  => FILTER_VALIDATE_INT,
      'cookie'      => FILTER_SANITIZE_STRING,
      'data'        => FILTER_UNSAFE_RAW,
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
      'logo_id'     => FILTER_VALIDATE_INT,
      'logo'        => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w-]+$/'
        ),
      ),
      'entity_id'   => FILTER_VALIDATE_INT,
      'attachment_id' => FILTER_VALIDATE_INT,
      'filename'    => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w\-\.]+$/'
         ),
      ), 
      'attachment_file' => FILTER_UNSAFE_RAW,
      'link_id'     => FILTER_VALIDATE_INT,
      'link'        => FILTER_VALIDATE_URL,
      'category_id' => FILTER_VALIDATE_INT,
      'category'    => FILTER_SANITIZE_STRING,
      'country_id'  => FILTER_VALIDATE_INT,
      'description' => FILTER_UNSAFE_RAW,
      'question'    => FILTER_UNSAFE_RAW,
      'flag'        => FILTER_UNSAFE_RAW,
      'answer'      => FILTER_UNSAFE_RAW,
      'hint'        => FILTER_UNSAFE_RAW,
      'points'      => FILTER_VALIDATE_INT,
      'bonus'       => FILTER_VALIDATE_INT,
      'bonus_dec'   => FILTER_VALIDATE_INT,
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
      'create_quiz',
      'update_quiz',
      'create_flag',
      'update_flag',
      'create_base',
      'update_base',
      'update_team',
      'delete_team',
      'delete_level',
      'update_session',
      'delete_session',
      'toggle_status_level',
      'toggle_status_team',
      'toggle_admin_team',
      'toggle_visible_team',
      'enable_country',
      'disable_country',
      'create_category',
      'delete_category',
      'enable_logo',
      'disable_logo',
      'create_attachment',
      'update_attachment',
      'delete_attachment',
      'create_link',
      'update_link',
      'delete_link'
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
