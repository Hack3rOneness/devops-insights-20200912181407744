<?hh // strict

class FailureLog extends Model {
  private function __construct(
    private int $id,
    private string $ts,
    private int $team_id,
    private int $level_id,
    private string $flag
    ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getTs(): string {
    return $this->ts;
  }

  public function getTeamId(): int {
    return $this->team_id;
  }

  public function getLevelId(): int {
    return $this->level_id;
  }

  public function getFlag(): string {
    return $this->flag;
  }

  private static function failurelogFromRow(array<string, string> $row): FailureLog {
    return new FailureLog(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      intval(must_have_idx($row, 'team_id')),
      intval(must_have_idx($row, 'level_id')),
      must_have_idx($row, 'flag'),
    );
  }

  // Log attempt on score.
  public static function logFailedScore(int $level_id, int $team_id, string $flag): void {
    $db = self::getDb();

    $sql = 'INSERT INTO failures_log (ts, level_id, team_id, flag) VALUES(NOW(), ?, ?, ?)';
    $elements = array($level_id, $team_id, $flag);
    $db->query($sql, $elements);
  }

  // Reset all failures.
  public static function resetFailures(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM failures_log WHERE id > 0';
    $db->query($sql);
  }

  // Get all scores.
  public static function allFailures(): array<FailureLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM failures_log ORDER BY ts DESC';
    $results = $db->query($sql);

    $failures = array();
    foreach ($results as $row) {
      $failures[] = self::failurelogFromRow($row);
    }

    return $failures;
  }

  // Get all scores by team.
  public static function allFailuresByTeam(int $team_id): array<FailureLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM failures_log WHERE team_id = ? ORDER BY ts DESC';
    $element = array($team_id);
    $results = $db->query($sql, $element);

    $failures = array();
    foreach ($results as $row) {
      $failures[] = self::failurelogFromRow($row);
    }

    return $failures;
  }
}
