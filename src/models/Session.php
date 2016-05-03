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

  private static function sessionFromRow(Map<string, string> $row): Session {
    return new Session(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'cookie'),
      must_have_idx($row, 'data'),
      must_have_idx($row, 'created_ts'),
      must_have_idx($row, 'last_access_ts'),
    );
  }

  // Create new session.
  public static async function genCreate(
    string $cookie,
    string $data,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO sessions (cookie, data, created_ts, last_access_ts) VALUES (%s, %s, NOW(), NOW())',
      $cookie,
      $data,
    );
  }

  // Retrieve the session by cookie.
  public static async function gen(
    string $cookie,
  ): Awaitable<Session> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM sessions WHERE cookie = %s LIMIT 1',
      $cookie,
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');

    return self::sessionFromRow($result->mapRows()[0]);
  }

  // Checks if session exists by cookie.
  public static async function genSessionExist(
    string $cookie,
  ): Awaitable<bool> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT COUNT(*) FROM sessions WHERE cookie = %s',
      $cookie,
    );
    invariant($result->numRows() === 1, 'Expected exactly one result');

    return intval(idx($result->mapRows()[0], 'COUNT(*)')) > 0;
  }

  // Update the session for a given cookie.
  public static async function genUpdate(
    string $cookie,
    string $data,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'UPDATE sessions SET last_access_ts = NOW(), data = %s WHERE cookie = %s LIMIT 1',
      $data,
      $cookie,
    );
  }

  // Delete the session for a given cookie.
  public static async function genDelete(
    string $cookie,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'DELETE FROM sessions WHERE cookie = %s LIMIT 1',
      $cookie,
    );
  }

  // Does cleanup of cookies.
  public static async function genCleanup(
    int $maxlifetime,
  ): Awaitable<void> {
    $db = await self::genDb();
    // Clean up expired sessions
    await $db->queryf(
      'DELETE FROM sessions WHERE UNIX_TIMESTAMP(last_access_ts) < %d',
      time() - $maxlifetime,
    );
    // Clean up empty sessions
    await $db->queryf(
      'DELETE FROM sessions WHERE data = ""',
    );
  }

  // All the sessions
  public static async function genAllSessions(): Awaitable<array<Session>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM sessions ORDER BY last_access_ts DESC',
    );

    $sessions = array();
    foreach ($result->mapRows() as $row) {
      $sessions[] = self::sessionFromRow($row);
    }

    return $sessions;
  }
}
