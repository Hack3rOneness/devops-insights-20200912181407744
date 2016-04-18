<?hh

class Request {
  private $input_method;
  private string $method;
  private $filters = array();
  private $actions = array();
  private $pages = array();

  public string $action = 'none';
  public string $page = 'main';
  public $parameters = array();

  public function __construct($filters, $actions, $pages) {
    $inputMethods = array(
      'POST' => INPUT_POST,
      'GET' => INPUT_GET,
    );
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->input_method = $inputMethods[$this->method];
    $this->filters = $filters;
    $this->actions = $actions;
    $this->pages = $pages;
  }

  public function processRequest() {
    $this->parameters = filter_input_array(
      $this->input_method,
      $this->filters[$this->method]
    );
    if ($this->parameters['action']) {
      $this->action = $this->parameters['action'];
    }
    if ($this->parameters['page']) {
      $this->page = $this->parameters['page'];
    }
    if ((!in_array($this->action, $this->actions))) {
      $this->action = 'none';
    }
    if ((!in_array($this->page, $this->pages))) {
      $this->page = 'main';
    }
  }
}
