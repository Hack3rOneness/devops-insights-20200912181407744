<?php


// Session expiration is 24 hours.
define("COOKIE_LIFE", 86400);

function session_open($session_path, $session_name) {
    return true;
}

function session_close() {
    return true;
}

function session_read($session_id) {
    $result = '';
    $session = db_retrieve_session($session_id);
    if ($session) {
        $result = $session['data'];
    } else {
        db_create_session($session_id, time());
    }
    return $result;
}

function session_write($session_id, $data) {
    db_update_session($session_id, time(), $data);
    return true;
}

function sess_destroy($session_id) {
    db_delete_session($session_id);
    return true;
}

function session_gc($session_maxlifetime) {
    db_clear_session($session_maxlifetime);
    return true;
}

function start_ctf_session() {
    session_set_save_handler("session_open", "session_close", "session_read", "session_write", "sess_destroy", "session_gc");
    session_name('facebookctf_session');
    session_set_cookie_params(COOKIE_LIFE, '/', DOMAIN, false, true); // Case: host as an ip address was not creating sessions properly
    session_start();
    setcookie(session_name(), session_id(), time()+COOKIE_LIFE, '/', DOMAIN, true, false); // ^
    header('Strict-Transport-Security: max-age=6000');
}

function check_if_admin() {
  if ($_SESSION['admin'] != '#yoloswaggins') {
    header('Location: https://'.DOMAIN.'/login.php');
    exit();
  }
}

function check_if_legal() {
  if ($_SESSION['legal_accept'] != 'done') {
    header('Location: https://'.DOMAIN.'/disclaimer.php');
    exit();
  }
}

?>
