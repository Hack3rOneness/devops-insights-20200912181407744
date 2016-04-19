<?hh // strict

abstract class AjaxController {
  abstract protected function getFilters(): array<string, mixed>;
  abstract protected function getActions(): array<string>;

  abstract protected function handleAction(string $action, array<string, mixed> $params): string;

  public function handleRequest(): string {
    list($action, $params) = $this->processRequest();
    return $this->handleAction($action, $params);
  }

  private function processRequest(): (string, array<string, mixed>) {
    $input_methods = array(
      'POST' => INPUT_POST,
      'GET' => INPUT_GET,
    );
    $method = getSERVER()->get('REQUEST_METHOD');
    invariant(is_string($method), 'REQUEST_METHOD must be a string');

    $filter = idx($this->getFilters(), $method);
    if ($filter === null) {
      // Method not supported
      return tuple('', array()); // TODO
    }

    $input_method = must_have_idx($input_methods, $method);
    $parameters = filter_input_array(
      $input_method,
      $filter,
    );

    $action = idx($parameters, 'action', 'main');
    if (!in_array($action, $this->getActions())) {
      $page = 'none';
    }

    return tuple($action, $parameters);
  }
}
