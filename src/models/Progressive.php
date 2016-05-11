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

  public static async function genGameStatus(
  ): Awaitable<bool> {
    $config = await Configuration::gen('game');
    return $config->getValue() === '1';
  }

  public static async function genCycle(
  ): Awaitable<int> {
    $config = await Configuration::gen('progressive_cycle');
    return intval($config->getValue());
  }
  
  private static function progressiveFromRow(Map<string, string> $row): Progressive {
    return new Progressive(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      must_have_idx($row, 'team_name'),
      intval(must_have_idx($row, 'points')),
      intval(must_have_idx($row, 'iteration')),
    );
  }

  // Progressive points.
  public static async function genProgressiveScoreboard(
    string $team_name,
  ): Awaitable<array<Progressive>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM progressive_log WHERE team_name = %s GROUP BY iteration ORDER BY points ASC',
      $team_name,
    );
    $progressive = array();
    foreach ($result->mapRows() as $row) {
      $progressive[] = self::progressiveFromRow($row);
    }
    return $progressive;
  }

  // Count how many iterations of the progressive scoreboard we have.
  public static async function genCount(): Awaitable<int> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT COUNT(DISTINCT(iteration)) AS C FROM progressive_log',
    );
    invariant($result->numRows() === 1, 'Expected exactly one result');
    return intval($result->mapRows()[0]['C']);
  }

  // Acquire the data for one iteration of the progressive scoreboard.
  public static async function genTake(): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO progressive_log (ts, team_name, points, iteration) (SELECT NOW(), name, points, (SELECT IFNULL(MAX(iteration)+1, 1) FROM progressive_log) FROM teams)',
    );
  }

  // Reset the progressive scoreboard.
  public static async function genReset(): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'DELETE FROM progressive_log WHERE id > 0',
    );
  }

  // Kick off the progressive scoreboard in the background.
  public static async function genRun(): Awaitable<void> {
    $document_root = must_have_string(Utils::getSERVER(), 'DOCUMENT_ROOT');
    $cmd = 'hhvm -vRepo.Central.Path=/tmp/.hhvm.hhbc_progressive '.$document_root.'/scripts/progressive.php > /dev/null 2>&1 & echo $!';
    $pid = shell_exec($cmd);
    await Control::genStartScriptLog(intval($pid), 'progressive', $cmd);
  }

  // Stop the progressive scoreboard process in the background
  public static async function genStop(): Awaitable<void> {
    // Kill running process
    $pid = await Control::genScriptPid('progressive');
    if ($pid > 0) {
      exec('kill -9 '.escapeshellarg(strval($pid)));
    }
    // Mark process as stopped
    await Control::genStopScriptLog($pid);
  }
}
