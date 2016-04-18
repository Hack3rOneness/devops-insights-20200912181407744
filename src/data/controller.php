<?hh

abstract class DataController {

  abstract public function generateData();

  public function jsonSend($data) {
    header('Content-Type: application/json');
    print json_encode($data, JSON_PRETTY_PRINT);
  }
}