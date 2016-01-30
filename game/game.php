<?php

class Game {

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
      'flag'        => FILTER_UNSAFE_RAW,
      'action'      => FILTER_SANITIZE_STRING,
    );
    $this->actions = array(
      'quiz_answer',
      'open_level',
      'submit_flag',
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
