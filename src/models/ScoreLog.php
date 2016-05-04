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

  private static function scorelogFromRow(Map<string, string> $row): ScoreLog {
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
  public static async function genAllScores(
  ): Awaitable<array<ScoreLog>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM scores_log ORDER BY ts DESC',
    );

    $scores = array();
    foreach ($result->mapRows() as $row) {
      $scores[] = self::scorelogFromRow($row);
    }

    return $scores;
  }

  // Reset all scores.
  public static async function genResetScores(): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'DELETE FROM scores_log WHERE id > 0',
    );
  }

  // Check if there is a previous score.
  public static async function genPreviousScore(
    int $level_id,
    int $team_id,
    bool $any_team,
  ): Awaitable<bool> {
    $db = await self::genDb();

    if ($any_team) {
      $result = await $db->queryf(
        'SELECT COUNT(*) FROM scores_log WHERE level_id = %d AND team_id IN (SELECT id FROM teams WHERE id != %d AND visible = 1)',
        $level_id,
        $team_id,
      );
    } else { 
      $result = await $db->queryf(
        'SELECT COUNT(*) FROM scores_log WHERE level_id = %d AND team_id = %d',
        $level_id,
        $team_id,
      );
    }

    if ($result->numRows() > 0) {
      invariant($result->numRows() === 1, 'Expected exactly one result');
      return intval($result->mapRows()[0]['COUNT(*)']) > 0;
    } else {
      return false;
    }
  }

  // Get all scores by team.
  public static async function genAllScoresByTeam(
    int $team_id,
  ): Awaitable<array<ScoreLog>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM scores_log WHERE team_id = %d ORDER BY ts DESC',
      $team_id,
    );

    $scores = array();
    foreach ($result->mapRows() as $row) {
      $scores[] = self::scorelogFromRow($row);
    }

    return $scores;
  }

  // Get all scores by type.
  public static async function genAllScoresByType(
    string $type,
  ): Awaitable<array<ScoreLog>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM scores_log WHERE type = %s ORDER BY ts DESC',
      $type,
    );

    $scores = array();
    foreach ($result->mapRows() as $row) {
      $scores[] = self::scorelogFromRow($row);
    }

    return $scores;
  }

  // Log successful score.
  public static async function genLogValidScore(
    int $level_id,
    int $team_id,
    int $points,
    string $type,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO scores_log (ts, level_id, team_id, points, type) VALUES (NOW(), %d, %d, %d, %s)',
      $level_id,
      $team_id,
      $points,
      $type,
    );
  }
}
