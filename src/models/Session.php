<?hh // strict

class Session extends Model {
  private function __construct(
    private int $id,
    private string $cookie,
    private string $data,
    private string $created_ts,
    private string $last_access_ts
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getCookie(): string {
    return $this->cookie;
  }

  public function getData(): string {
    return $this->data;
  }

  public function getCreatedTs(): string {
    return $this->created_ts;
  }

  public function getLastAccessTs(): string {
    return $this->last_access_ts;
  }

  public function getTeamId(): int {
    // TODO: sessions do not serialize with standard php serialization
    $sess_data = unserialize($this->data);
    return intval($sess_data->team_id);
  }

  public function getTeamName(): string {
    // TODO: sessions do not serialize with standard php serialization
    $sess_data = unserialize($this->data);
    return $sess_data->name;
  }

  private static function sessionFromRow(array<string, string> $row): Session {
    return new Session(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'cookie'),
      must_have_idx($row, 'data'),
      must_have_idx($row, 'created_ts'),
      must_have_idx($row, 'last_access_ts'),
    );
  }

  // Create new session.
  public static function create(string $cookie, string $data): void {
    $db = self::getDb();
    $sql = 'INSERT INTO sessions (cookie, data, created_ts, last_access_ts) VALUES (?, ?, NOW(), NOW())';
    $elements = array($cookie, $data);
    $db->query($sql, $elements);
  }

  // Retrieve the session by cookie.
  public static function get(string $cookie): Session {
    $db = self::getDb();
    $sql = 'SELECT * FROM sessions WHERE cookie = ? LIMIT 1';
    $element = array($cookie);
    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return self::sessionFromRow(firstx($results));
  }

  // Checks if session exists by cookie.
  public static function sessionExist(string $cookie): bool {
    $db = self::getDb();
    $sql = 'SELECT COUNT(*) FROM sessions WHERE cookie = ?';
    $element = array($cookie);
    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['COUNT(*)']) > 0;
  }

  // Update the session for a given cookie.
  public static function update(string $cookie, string $data): void {
    $db = self::getDb();
    $sql = 'UPDATE sessions SET last_access_ts = NOW(), data = ? WHERE cookie = ? LIMIT 1';
    $elements = array($data, $cookie);
    $db->query($sql, $elements);
  }

  // Delete the session for a given cookie.
  public static function delete(string $cookie): void {
    $db = self::getDb();
    $sql = 'DELETE FROM sessions WHERE cookie = ? LIMIT 1';
    $element = array($cookie);
    $db->query($sql, $element);
  }

  // Does cleanup of cookies.
  public static function cleanup(int $maxlifetime): void {
    $db = self::getDb();
    // Clean up expired sessions
    $gc_time = time() - $maxlifetime;
    $sql = 'DELETE FROM sessions WHERE UNIX_TIMESTAMP(last_access_ts) < ?';
    $element = array($gc_time);
    $db->query($sql, $element);
    // Clean up empty sessions
    $sql = 'DELETE FROM sessions WHERE data = ""';
    $db->query($sql);
  }

  // All the sessions
  public static function allSessions(): array<Session> {
    $db = self::getDb();
    $sql = 'SELECT * FROM sessions ORDER BY last_access_ts DESC';
    $results = $db->query($sql);

    $sessions = array();
    foreach ($results as $row) {
      $sessions[] = self::sessionFromRow($row);
    }

    return $sessions;
  }
}