<?hh // strict

class HintLog extends Model {
  private function __construct(
    private int $id,
    private string $ts,
    private int $level_id,
    private int $team_id,
    private int $penalty
    ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getTs(): string {
    return $this->ts;
  }

  public function getLevelId(): int {
    return $this->level_id;
  }

  public function getTeamId(): int {
    return $this->team_id;
  }

  public function getPenalty(): int {
    return $this->penalty;
  }

  private static function hintlogFromRow(array<string, string> $row): HintLog {
    return new HintLog(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      intval(must_have_idx($row, 'level_id')),
      intval(must_have_idx($row, 'team_id')),
      intval(must_have_idx($row, 'penalty')),
    );
  }

  // Log hint request hint.
  public static function logGetHint(int $level_id, int $team_id, int $penalty): void {
    $db = self::getDb();

    $sql = 'INSERT INTO hints_log (ts, level_id, team_id, penalty) VALUES (NOW(), ?, ?, ?)';
    $elements = array($level_id, $team_id, $penalty);
    $db->query($sql, $elements);
  }

  public static function resetHints(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM hints_log WHERE id > 0';
    $db->query($sql);
  }

  // Check if there is a previous hint.
  public static function previousHint(int $level_id, int $team_id, bool $any_team): bool {
    $db = self::getDb();

    $sql = ($any_team)
      ? 'SELECT COUNT(*) FROM hints_log WHERE level_id = ? AND team_id != ?'
      : 'SELECT COUNT(*) FROM hints_log WHERE level_id = ? AND team_id = ?';
    $elements = array($level_id, $team_id);
    $result = $db->query($sql, $elements);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return intval(firstx($result)['COUNT(*)']) > 0;
    } else {
      return false;
    }
  }

  // Get all scores.
  public static function allHints(): array<HintLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM hints_log ORDER BY ts DESC';
    $results = $db->query($sql);

    $hints = array();
    foreach ($results as $row) {
      $hints[] = self::hintlogFromRow($row);
    }

    return $hints;
  }

  // Get all scores by team.
  public static function allHintsByTeam(int $team_id): array<HintLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM hints_log WHERE team_id = ? ORDER BY ts DESC';
    $element = array($team_id);
    $results = $db->query($sql, $element);

    $hints = array();
    foreach ($results as $row) {
      $hints[] = self::hintlogFromRow($row);
    }

    return $hints;
  }
}
