<?hh // strict

class Token extends Model {
  private function __construct(
    private int $id,
    private int $used,
    private int $team_id, 
    private string $token,
    private string $created_ts,
    private string $use_ts
    ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getUsed(): int {
    return $this->used;
  }

  public function getTeamId(): int {
    return $this->team_id;
  }

  public function getToken(): string {
    return $this->token;
  }

  public function getCreatedTs(): string {
    return $this->created_ts;
  }

  public function getUseTs(): string {
    return $this->use_ts;
  }

  private static function tokenFromRow(array<string, string> $row): Token {
    return new Token(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'used')),
      intval(must_have_idx($row, 'team_id')),
      must_have_idx($row, 'token'),
      must_have_idx($row, 'created_ts'),
      must_have_idx($row, 'use_ts'),
    );
  }

  private static function generate(): string {
    $token_len = 15;
    $crypto_strong = True;
    return md5(
      base64_encode(
        openssl_random_pseudo_bytes(
          $token_len,
          $crypto_strong,
        )
      )
    );
  }

  // Create token.
  public static function create(): void {
    $db = self::getDb();
    $tokens = array();
    $query = array();
    $token_number = 50;
    for ($i = 0; $i < $token_number; $i++) {
      $token = self::generate();
      $sql = 'INSERT INTO registration_tokens (token, created_ts) VALUES (?, NOW())';
      $element = array($token);
      $db->query($sql, $element);
    }
  }

  public static function export(): void {
    $db = self::getDb();
    $sql = 'SELECT * FROM registration_tokens WHERE used = 0';
    $tokens = $db->query($sql);
    // TODO
  }

  public static function delete(string $token): void {
    $db = self::getDb();
    $sql = 'DELETE from registration_tokens WHERE token = ? LIMIT 1';
    $element = array($token);
    $db->query($sql, $element);
  }

  // Get all tokens.
  public static function allTokens(): array<Token> {
    $db = self::getDb();
    $sql = 'SELECT * FROM registration_tokens';
    $results = $db->query($sql);

    $tokens = array();
    foreach ($results as $row) {
      $tokens[] = self::tokenFromRow($row);
    }

    return $tokens;
  }

  // Get all available tokens.
  public static function allAvailableTokens(): array<Token> {
    $db = self::getDb();
    $sql = 'SELECT * FROM registration_tokens WHERE used = 0';
    $results = $db->query($sql);

    $tokens = array();
    foreach ($results as $row) {
      $tokens[] = self::tokenFromRow($row);
    }

    return $tokens;
  }

  // Check to see if the level is active.
  public static function check(string $token): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM registration_tokens WHERE used = 0 AND token = ?';
    $element = array($token);
    $result = $db->query($sql, $element);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return (intval(firstx($result)['COUNT(*)']) > 0);
    } else {
      return false;
    }
  }

  // Use a token for a team registration.
  public static function use(string $token, int $team_id): void {
    $db = self::getDb();
    $sql = 'UPDATE registration_tokens SET used = 1, team_id = ?, use_ts = NOW() WHERE token = ? LIMIT 1';
    $elements = array($team_id, $token);
    $db->query($sql, $elements);
  }
}
