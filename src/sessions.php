<?hh

function sess_start() {
  $sess_name = 'FBCTF';
  $sess_lifetime = 86400;
  $sess_domain = $_SERVER['SERVER_NAME'];
  $sess_secure = true;
  $sess_httponly = true;
  $sess_path = '/';

  session_set_save_handler(
    'sess_open',
    'sess_close',
    'sess_read',
    'sess_write',
    'sess_destroy',
    'sess_gc'
  );
  session_name($sess_name);
  session_set_cookie_params(
    $sess_lifetime,
    $sess_path,
    $sess_domain,
    $sess_secure,
    $sess_httponly
  );
  session_start();
  setcookie(
    $sess_name,
    session_id(),
    time() + $sess_lifetime,
    $sess_path,
    $sess_domain,
    $sess_secure,
    $sess_httponly
  );
}

function sess_open($path, $name) {
  return true;
}

function sess_close() {
  return true;
}

function sess_read($session_id) {
  $db = Db::getInstance();
  $sql = 'SELECT data FROM sessions WHERE cookie = ? LIMIT 1';
  $element = array($session_id);
  $data = $db->query($sql, $element);
  if ($data) {
    return $data[0]['data'];
  } else {
    $sql = 'INSERT INTO sessions (cookie, created_ts, last_access_ts) VALUES (?, NOW(), NOW())';
    $element = array($session_id);
    $db->query($sql, $element);
    return '';
  }
}

function sess_write($session_id, $data) {
  $db = Db::getInstance();
  $sql = 'UPDATE sessions SET last_access_ts = NOW(), data = ? WHERE cookie = ? LIMIT 1';
  $elements = array($data, $session_id);
  $db->query($sql, $elements);
  return true;
}

function sess_destroy($session_id) {
  $db = Db::getInstance();
  $sql = 'DELETE FROM sessions WHERE cookie = ? LIMIT 1';
  $element = array($session_id);
  $db->query($sql, $element);
  return true;
}

function sess_gc($session_maxlifetime) {
  $gc_time = time() - $session_maxlifetime;
  $db = Db::getInstance();
  $sql = 'DELETE FROM sessions WHERE UNIX_TIMESTAMP(last_access_ts) < ?';
  $element = array($gc_time);
  $db->query($sql, $element);
  return true;
}

function sess_all() {
  $db = Db::getInstance();
  $sql = 'SELECT * FROM sessions ORDER BY last_access_ts DESC';
  return $db->query($sql);
}

function sess_set($name, $value) {
  $_SESSION[$name] = $value;
}

function sess_logout() {
  session_destroy();
  unset($_SESSION['team_id']);
  start_page();
  exit();
}

function sess_active() {
  return (bool)(isset($_SESSION['team_id']));
}

function sess_enforce_login() {
  if (!isset($_SESSION['team_id'])) {
    start_page();
    exit();
  }
}

function sess_enforce_admin() {
  if (!isset($_SESSION['admin'])) {
    start_page();
    exit();
  }
}

function sess_admin() {
  return (bool)(isset($_SESSION['admin']));
}

function sess_team() {
  return $_SESSION['team_id'];
}

function sess_teamname() {
  return $_SESSION['name'];
}

function sess_csrf_token() {
  return $_SESSION['csrf_token'];
}
