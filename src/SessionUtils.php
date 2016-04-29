<?hh // strict

class SessionUtils {
  private static string $s_name = 'FBCTF';
  private static int $s_lifetime = 3600;
  private static bool $s_secure = true;
  private static bool $s_httponly = true;
  private static string $s_path = '/';

  private function __construct() {}

  private function __clone(): void {}

  public static function sessionStart(): void {
    session_set_save_handler(
      array(__CLASS__, 'open'),
      array(__CLASS__, 'close'),
      array(__CLASS__, 'read'),
      array(__CLASS__, 'write'),
      array(__CLASS__, 'destroy'),
      array(__CLASS__, 'gc')
    );
    session_name(self::$s_name);
    session_set_cookie_params(
      self::$s_lifetime,
      self::$s_path,
      must_have_string(Utils::getSERVER(), 'SERVER_NAME'),
      self::$s_secure,
      self::$s_httponly
    );
    session_start();
    setcookie(
      self::$s_name,
      session_id(),
      time() + self::$s_lifetime,
      self::$s_path,
      must_have_string(Utils::getSERVER(), 'SERVER_NAME'),
      self::$s_secure,
      self::$s_httponly
    );
  }

  public function open(string $path, string $name): bool {
    return true;
  }

  public function close(): bool {
    return true;
  }

  public function read(string $cookie): string {
    if (Session::sessionExist($cookie)) {
      $session = Session::get($cookie);
      return $session->getData();
    } else {
      return '';
    }
  }

  public function write(string $cookie, string $data): bool {
    if (Session::sessionExist($cookie)) {
      Session::update($cookie, $data);
    } else {
      Session::create($cookie, $data);
    }
    return true;
  }

  public function destroy(string $cookie): bool {
    Session::delete($cookie);
    return true;
  }

  public function gc(int $maxlifetime): bool {
    Session::cleanup($maxlifetime);
    return true;
  }

  public static function sessionSet(string $name, string $value): void {
    $_SESSION[$name] = $value;
  }

  public static function sessionLogout(): void {
    session_destroy();

    //unset(must_have_string(Utils::getSESSION(), 'team_id'));
    throw new IndexRedirectException();
  }

  public static function sessionActive(): bool {
    return (bool)(array_key_exists('team_id', $_SESSION));
  }

  public static function enforceLogin(): void {
    if (!array_key_exists('team_id', $_SESSION)) {
      throw new IndexRedirectException();
    }
  }

  public static function enforceAdmin(): void {
    if (!array_key_exists('admin', $_SESSION)) {
      throw new IndexRedirectException();
    }
  }

  public static function sessionAdmin(): bool {
    $admin = must_have_string($_SESSION, 'admin');
    return (bool)(intval($admin));
  }

  public static function sessionTeam(): int {
    return intval(must_have_string($_SESSION, 'team_id'));
  }

  public static function sessionTeamName(): string {
    return must_have_string($_SESSION, 'name');
  }

  public static function CSRFToken(): string {
    return must_have_string($_SESSION, 'csrf_token');
  }
}