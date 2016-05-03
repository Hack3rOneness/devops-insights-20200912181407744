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

  private static function hintlogFromRow(Map<string, string> $row): HintLog {
    return new HintLog(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      intval(must_have_idx($row, 'level_id')),
      intval(must_have_idx($row, 'team_id')),
      intval(must_have_idx($row, 'penalty')),
    );
  }

  // Log hint request hint.
  public static async function genLogGetHint(
    int $level_id,
    int $team_id,
    int $penalty,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO hints_log (ts, level_id, team_id, penalty) VALUES (NOW(), %d, %d, %d)',
      $level_id,
      $team_id,
      $penalty,
    );
  }

  public static async function genResetHints(
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'DELETE FROM hints_log WHERE id > 0',
    );
  }

  // Check if there is a previous hint.
  public static async function genPreviousHint(
    int $level_id,
    int $team_id,
    bool $any_team,
  ): Awaitable<bool> {
    $db = await self::genDb();

    if ($any_team) {
      $result = await $db->queryf(
        'SELECT COUNT(*) FROM hints_log WHERE level_id = %d AND team_id != %d',
        $level_id,
        $team_id,
      );
    } else {
      $result = await $db->queryf(
        'SELECT COUNT(*) FROM hints_log WHERE level_id = %d AND team_id = %d',
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

  // Get all scores.
  public static async function genAllHints(
  ): Awaitable<array<HintLog>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM hints_log ORDER BY ts DESC',
    );

    $hints = array();
    foreach ($result->mapRows() as $row) {
      $hints[] = self::hintlogFromRow($row);
    }

    return $hints;
  }

  // Get all scores by team.
  public static async function genAllHintsByTeam(
    int $team_id,
  ): Awaitable<array<HintLog>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM hints_log WHERE team_id = %d ORDER BY ts DESC',
      $team_id,
    );

    $hints = array();
    foreach ($result->mapRows() as $row) {
      $hints[] = self::hintlogFromRow($row);
    }

    return $hints;
  }
}
