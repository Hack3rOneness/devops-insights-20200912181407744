<?php

require_once('db.php');

class Session {
  private $db;
  private static $instance;
  private $cookie_life;
  private $path;
  private $domain;
  private $secure;
  private $httponly;
  private $sname;

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __clone() { }

  private function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->connected) {
      $this->db->connect();
    }
    $this->cookie_life = 86400;
    $this->path = '/';
    $this->domain = $_SERVER['SERVER_NAME'];
    $this->secure = false;
    $this->httponly = false;
    $this->sname = 'FBCTF';
  }

  public function start() {
    session_set_save_handler(
      array($this, '_open'),
      array($this, '_close'),
      array($this, '_read'),
      array($this, '_write'),
      array($this, '_destroy'),
      array($this, '_gc')
    );
    session_name($this->sname);
    session_set_cookie_params(
      $this->cookie_life,
      $this->path,
      $this->domain,
      $this->secure,
      $this->httponly
    );
    session_start();
    setcookie(
      session_name(),
      session_id(),
      time() + $this->cookie_life,
      $this->path,
      $this->domain,
      $this->secure,
      $this->httponly
    );
    //header('Strict-Transport-Security: max-age=6000');
  }

  public function enforce() {
    if (!isset($_SESSION['team_id'])) {
      $sql = 'DELETE FROM sessions WHERE data = ""';
      $this->db->query($sql);
      header('Location: /index.html');
      die();
    }
  }

  public function __set($name, $value) {
    $_SESSION[$name] = $value;
  }

  public function __get($name) {
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    }
  }

  public function __isset($name) {
    return isset($_SESSION[$name]);
  }

  public function __unset($name) {
    unset($_SESSION[$name]);
  }

  public function _open($path, $name) {
    return true;
  }

  public function _close() {
    return true;
  }

  public function _read($session_id) {
    $sql = 'SELECT data FROM sessions WHERE cookie = ? LIMIT 1';
    $element = array($session_id);
    $data = $this->db->query($sql, $element);
    if ($data) {
      error_log($data['0']['data']);
      return $data['0']['data'];
    } else {
      $sql = 'INSERT INTO sessions (cookie, last_access_ts) VALUES (?, NOW())';
      $element = array($session_id);
      $this->db->query($sql, $element);
      return '';
    }
  }

  public function _write($session_id, $data) {
    $sql = 'UPDATE sessions SET last_access_ts = NOW(), data = ? WHERE cookie = ? LIMIT 1';
    $elements = array($data, $session_id);
    $this->db->query($sql, $elements);
    return true;
  }

  public function _destroy($session_id) {
    $sql = 'DELETE FROM sessions WHERE cookie = ? LIMIT 1';
    $element = array($session_id);
    $this->db->query($sql, $element);
    return true;
  }

  public function _gc($session_maxlifetime) {
    $gc_time = time() - $session_maxlifetime;
    $sql = 'DELETE FROM sessions WHERE UNIX_TIMESTAMP(last_access_ts) < ?';
    $element = array($gc_time);
    $this->db->query($sql, $element);
    return true;
  }
}

?>
