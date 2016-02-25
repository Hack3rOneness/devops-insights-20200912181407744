<?php

class GameRequests {

  private $input_method;
  private $method;
  private $filters = array();
  private $actions = array();

  public $action;
  public $parameters = array();

  function __construct() {
    $inputMethods = array('POST' => INPUT_POST);
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->input_method = $inputMethods[$this->method];
    $this->filters = array(
      'level_id'    => FILTER_VALIDATE_INT,
      'answer'        => FILTER_UNSAFE_RAW,
      'action'      => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w-]+$/'
        ),
      )
    );
    $this->actions = array(
      'open_level',
      'answer_level',
    );
  }

  public function processGame() {
    $this->parameters = filter_input_array($this->input_method, $this->filters);
    if ($this->parameters) {
      $this->action = $this->parameters['action'];
    }
    if ((!in_array($this->action, $this->actions))) {
      $this->action = 'none';
    }
  }
}
