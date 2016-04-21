<?hh // strict

class Progressive extends Model {
  private function __construct(
    private int $id,
    private string $ts,
    private string $team_name,
    private int $points,
    private int $iteration
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getTs(): string {
    return $this->ts;
  }

  public function getTeamName(): string {
    return $this->team_name;
  }

  public function getPoints(): int {
    return $this->points;
  }

  public function getIteration(): int {
    return $this->iteration;
  }

  public static function getGameStatus(): bool {
    return (Configuration::get('game')->getValue() === '1');
  }

  public static function getCycle(): int {
    return intval(Configuration::get('progressive_cycle')->getValue());
  }

  private static function progressiveFromRow(array<string, string> $row): Progressive {
    return new Progressive(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      must_have_idx($row, 'team_name'),
      intval(must_have_idx($row, 'points')),
      intval(must_have_idx($row, 'iteration')),
    );
  }

  // Progressive points.
  public static function progressiveScoreboard(string $team_name): array<Progressive> {
    $db = self::getDb();

    $sql = 'SELECT * FROM progressive_log WHERE team_name = ? GROUP BY iteration ORDER BY points ASC';
    $element = array($team_name);
    $results = $db->query($sql, $element);

    $progressive = array();
    foreach ($results as $row) {
      $progressive[] = self::progressiveFromRow($row);
    }

    return $progressive;
  }

  // Count how many iterations of the progressive scoreboard we have.
  public static function count(): int {
    $db = self::getDb();

    $sql = 'SELECT COUNT(DISTINCT(iteration)) AS C FROM progressive_log';
    $results = $db->query($sql);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['C']);
  }

  // Acquire the data for one iteration of the progressive scoreboard.
  public static function take(): void {
    $db = self::getDb();

    $sql = 'INSERT INTO progressive_log (ts, team_name, points, iteration) (SELECT NOW(), name, points, (SELECT IFNULL(MAX(iteration)+1, 1) FROM progressive_log) FROM teams)';
    $db->query($sql);
  }

  // Reset the progressive scoreboard.
  public static function reset(): void {
    $db = self::getDb();

    $sql = 'DELETE FROM progressive_log WHERE id > 0';
    $db->query($sql);
  }

  // Kick off the progressive scoreboard in the background.
  public static function run(): void {
    $cmd = 'hhvm -vRepo.Central.Path=/tmp/.hhvm.hhbc_progressive '.$_SERVER['DOCUMENT_ROOT'].'/scripts/progressive.php &';
    shell_exec($cmd);
  }
}
