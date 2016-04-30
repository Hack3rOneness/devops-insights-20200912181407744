<?hh // strict

class ScoreLog extends Model {
  private function __construct(
    private int $id,
    private string $ts,
    private int $team_id,
    private int $points,
    private int $level_id,
    private string $type
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

  public function getPoints(): int {
    return $this->points;
  }

  public function getLevelId(): int {
    return $this->level_id;
  }

  public function getType(): string {
    return $this->type;
  }

  private static function scorelogFromRow(array<string, string> $row): ScoreLog {
    return new ScoreLog(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      intval(must_have_idx($row, 'team_id')),
      intval(must_have_idx($row, 'points')),
      intval(must_have_idx($row, 'level_id')),
      must_have_idx($row, 'type'),
    );
  }

  // Get all scores.
  public static function allScores(): array<ScoreLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM scores_log ORDER BY ts DESC';
    $results = $db->query($sql);

    $scores = array();
    foreach ($results as $row) {
      $scores[] = self::scorelogFromRow($row);
    }

    return $scores;
  }

  // Reset all scores.
  public static function resetScores(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM scores_log WHERE id > 0';
    $db->query($sql);
  }

  // Check if there is a previous score.
  public static function previousScore(int $level_id, int $team_id, bool $any_team): bool {
    $db = self::getDb();
    $sql = ($any_team)
      ? 'SELECT COUNT(*) FROM scores_log WHERE level_id = ? AND team_id IN (SELECT id FROM teams WHERE id != ? AND visible = 1)'
      : 'SELECT COUNT(*) FROM scores_log WHERE level_id = ? AND team_id = ?';
    $elements = array($level_id, $team_id);
    $result = $db->query($sql, $elements);
    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return intval(firstx($result)['COUNT(*)']) > 0;
    } else {
      return false;
    }
  }

  // Get all scores by team.
  public static function allScoresByTeam(int $team_id): array<ScoreLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM scores_log WHERE team_id = ? ORDER BY ts DESC';
    $element = array($team_id);
    $results = $db->query($sql, $element);

    $scores = array();
    foreach ($results as $row) {
      $scores[] = self::scorelogFromRow($row);
    }

    return $scores;
  }

  // Get all scores by type.
  public static function allScoresByType(string $type): array<ScoreLog> {
    $db = self::getDb();
    $sql = 'SELECT * FROM scores_log WHERE type = ? ORDER BY ts DESC';
    $element = array($type);
    $results = $db->query($sql, $element);

    $scores = array();
    foreach ($results as $row) {
      $scores[] = self::scorelogFromRow($row);
    }

    return $scores;
  }

  // Log successful score.
  public static function logValidScore(int $level_id, int $team_id, int $points, string $type): void {
    $db = self::getDb();

    $sql = 'INSERT INTO scores_log (ts, level_id, team_id, points, type) VALUES (NOW(), ?, ?, ?, ?)';
    $elements = array($level_id, $team_id, $points, $type);
    $db->query($sql, $elements);
  }
}
