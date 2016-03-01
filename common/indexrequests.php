<?hh

class IndexRequests {
  private $input_method;
  private $method;
  private $filters = array();
  private $actions = array();

  public $action;
  public $parameters = array();

  public function __construct() {
    $inputMethods = array('POST' => INPUT_POST);
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->input_method = $inputMethods[$this->method];
    $this->filters = array(
      'team_id'     => FILTER_VALIDATE_INT,
      'teamname'    => FILTER_SANITIZE_STRING,
      'password'    => FILTER_UNSAFE_RAW,
      'logo'        => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w-]+$/'
        ),
      ),
      'action'      => array(
        'filter'      => FILTER_VALIDATE_REGEXP,
        'options'     => array(
          'regexp'      => '/^[\w-]+$/'
        ),
      )
    );
    $this->actions = array(
      'register_team',
      'login_team',
    );
  }

  public function processIndex() {
    $this->parameters = filter_input_array($this->input_method, $this->filters);
    if ($this->parameters) {
      $this->action = $this->parameters['action'];
    }
    if ((!in_array($this->action, $this->actions))) {
      $this->action = 'none';
    }
  }
}
