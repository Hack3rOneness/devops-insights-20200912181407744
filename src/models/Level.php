<?hh // strict

class Level extends Model {
  private function __construct(
    private int $id,
    private int $active,
    private string $type,
    private string $title,
    private string $description,
    private int $entity_id,
    private int $category_id,
    private int $points,
    private int $bonus,
    private int $bonus_dec,
    private int $bonus_fix,
    private string $flag,
    private string $hint,
    private int $penalty,
    private string $created_ts
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getActive(): bool {
    return $this->active === 1;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getTitle(): string {
    return $this->title;
  }

  public function getDescription(): string {
    return $this->description;
  }

  public function getEntityId(): int {
    return $this->entity_id;
  }

  public function getCategoryId(): int {
    return $this->category_id;
  }

  public function getPoints(): int {
    return $this->points;
  }

  public function getBonus(): int {
    return $this->bonus;
  }

  public function getBonusDec(): int {
    return $this->bonus_dec;
  }

  public function getBonusFix(): int {
    return $this->bonus_fix;
  }

  public function getFlag(): string {
    return $this->flag;
  }

  public function getHint(): string {
    return $this->hint;
  }

  public function getPenalty(): int {
    return $this->penalty;
  }

  public function getCreatedTs(): string {
    return $this->created_ts;
  }

  private static function levelFromRow(array<string, string> $row): Level {
    return new Level(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'active')),
      must_have_idx($row, 'type'),
      must_have_idx($row, 'title'),
      must_have_idx($row, 'description'),
      intval(must_have_idx($row, 'entity_id')),
      intval(must_have_idx($row, 'category_id')),
      intval(must_have_idx($row, 'points')),
      intval(must_have_idx($row, 'bonus')),
      intval(must_have_idx($row, 'bonus_dec')),
      intval(must_have_idx($row, 'bonus_fix')),
      must_have_idx($row, 'flag'),
      must_have_idx($row, 'hint'),
      intval(must_have_idx($row, 'penalty')),
      must_have_idx($row, 'created_ts'),
    );
  }

  // Retrieve the level that is using one country
  public static function whoUses(int $country_id): ?Level {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels WHERE entity_id = ? AND active = 1 LIMIT 1';
    $element = array($country_id);
    $result = $db->query($sql, $element);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return self::levelFromRow(firstx($result));
    } else {
      return null;
    }
  }

  // Check to see if the level is active.
  public static function checkStatus(int $level_id): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM levels WHERE id = ? AND active = 1 LIMIT 1';
    $element = array($level_id);
    $result = $db->query($sql, $element);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return (intval(firstx($result)['COUNT(*)']) > 0);
    } else {
      return false;
    }
  }

  // Check to see if the level is a base.
  public static function checkBase(int $level_id): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM levels WHERE id = ? AND active = 1 AND type = "base" LIMIT 1';
    $element = array($level_id);
    $result = $db->query($sql, $element);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return (intval(firstx($result)['COUNT(*)']) > 0);
    } else {
      return false;
    }
  }

  // Create a team and return the created level id.
  public static function create(
    string $type,
    string $title,
    string $description,
    int $entity_id,
    int $category_id,
    int $points,
    int $bonus,
    int $bonus_dec,
    int $bonus_fix,
    string $flag,
    string $hint,
    int $penalty
  ): int {
    $db = self::getDb();

    if ($entity_id === 0) {
      $ent_id = Country::randomAvailableCountryId();
    } else {
      $ent_id = $entity_id;
    }
    $sql = 'INSERT INTO levels '.
      '(type, title, description, entity_id, category_id, points, bonus, bonus_dec, bonus_fix, flag, hint, penalty, created_ts) '.
      'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());';
    $elements = array(
      $type,
      $title,
      $description,
      $ent_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus_fix,
      $flag,
      $hint,
      $penalty
    );
    $db->query($sql, $elements);

    // Mark entity as used
    Country::setUsed($ent_id, true);

    // Return the newly created level_id
    $sql = 'SELECT id FROM levels WHERE title = ? AND description = ? AND entity_id = ? AND flag = ? AND category_id = ? LIMIT 1';
    $element = array($title, $description, $ent_id, $flag, $category_id);
    $result = $db->query($sql, $element);

    invariant(count($result) === 1, 'Expected exactly one result');
    return intval(must_have_idx(firstx($result), 'id'));
  }

  // Create a flag level.
  public static function createFlag(
    string $title,
    string $description,
    string $flag,
    int $entity_id,
    int $category_id,
    int $points,
    int $bonus,
    int $bonus_dec,
    string $hint,
    int $penalty
  ): int {
    return self::create(
      'flag',
      $title,
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $flag,
      $hint,
      $penalty
    );
  }

  // Update a flag level.
  public static function updateFlag(
    string $title,
    string $description,
    string $flag,
    int $entity_id,
    int $category_id,
    int $points,
    int $bonus,
    int $bonus_dec,
    string $hint,
    int $penalty,
    int $level_id
  ): void {
    self::update(
      $title,
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $flag,
      $hint,
      $penalty,
      $level_id
    );
  }

  // Create a quiz level.
  public static function createQuiz(
    string $title,
    string $question,
    string $answer,
    int $entity_id,
    int $points,
    int $bonus,
    int $bonus_dec,
    string $hint,
    int $penalty
  ): int {
    $db = self::getDb();

    $sql = 'SELECT id FROM categories WHERE category = "Quiz" LIMIT 1';
    $category_id = intval(must_have_idx(firstx($db->query($sql)), 'id'));
    return self::create(
      'quiz',
      $title,
      $question,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $answer,
      $hint,
      $penalty
    );
  }

  // Update a quiz level.
  public static function updateQuiz(
    string $title,
    string $question,
    string $answer,
    int $entity_id,
    int $points,
    int $bonus,
    int $bonus_dec,
    string $hint,
    int $penalty,
    int $level_id
  ): void {
    $db = self::getDb();

    $sql = 'SELECT id FROM categories WHERE category = "Quiz" LIMIT 1';
    $category_id = intval(must_have_idx(firstx($db->query($sql)), 'id'));
    self::update(
      $title,
      $question,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $answer,
      $hint,
      $penalty,
      $level_id
    );
  }

  // Create a base level.
  public static function createBase(
    string $title,
    string $description,
    int $entity_id,
    int $category_id,
    int $points,
    int $bonus,
    string $hint,
    int $penalty
  ): int {
    return self::create(
      'base',
      $title,
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      0,
      $bonus,
      '',
      $hint,
      $penalty
    );
  }

  // Update a base level.
  public static function updateBase(
    string $title,
    string $description,
    int $entity_id,
    int $category_id,
    int $points,
    int $bonus,
    string $hint,
    int $penalty,
    int $level_id
  ): void {
    self::update(
      $title,
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      0,
      $bonus,
      '',
      $hint,
      $penalty,
      $level_id
    );
  }

  // Update level.
  public static function update(
    string $title,
    string $description,
    int $entity_id,
    int $category_id,
    int $points,
    int $bonus,
    int $bonus_dec,
    int $bonus_fix,
    string $flag,
    string $hint,
    int $penalty,
    int $level_id
  ): void {
    $db = self::getDb();

    if ($entity_id === 0) {
      $ent_id = Country::randomAvailableCountryId();
    } else {
      $ent_id = $entity_id;
    }
    $sql = 'UPDATE levels SET title = ?, description = ?, entity_id = ?, category_id = ?, points = ?, '.
      'bonus = ?, bonus_dec = ?, bonus_fix = ?, flag = ?, hint = ?, '.
      'penalty = ? WHERE id = ? LIMIT 1';
    $elements = array(
      $title,
      $description,
      $ent_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus_fix,
      $flag,
      $hint,
      $penalty,
      $level_id
    );
    $db->query($sql, $elements);

    // Make sure entities are consistent
    Country::usedAdjust();
  }

  // Delete level.
  public static function delete(int $level_id): void {
    $db = self::getDb();

    // Free country first.
    $level = Level::getLevel($level_id);
    Country::setUsed($level->getEntityId(), false);

    $sql = 'DELETE FROM levels WHERE id = ? LIMIT 1';
    $elements = array($level_id);
    $db->query($sql, $elements);
  }

  // Enable or disable level by passing 1 or 0.
  public static function setStatus(int $level_id, bool $active): void {
    $db = self::getDb();

    $sql = 'UPDATE levels SET active = ? WHERE id = ? LIMIT 1';
    $elements = array($active, $level_id);
    $db->query($sql, $elements);
  }

  // Enable or disable levels by type.
  public static function setStatusType(bool $active, string $type): void {
    $db = self::getDb();

    $sql = 'UPDATE levels SET active = ? WHERE type = ?';
    $elements = array($active, $type);
    $db->query($sql, $elements);
  }

  // Enable or disable all levels.
  public static function setStatusAll(bool $active, string $type): void {
    $db = self::getDb();

    if ($type === 'all') {
      $sql = 'UPDATE levels SET active = ? WHERE id > 0';
      $elements = array($active);
    } else {
      $sql = 'UPDATE levels SET active = ? WHERE type = ?';
      $elements = array($active, $type);
    }
    $db->query($sql, $elements);
  }

  // All levels.
  public static function allLevels(): array<Level> {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels';
    $results = $db->query($sql);

    $levels = array();
    foreach ($results as $row) {
      $levels[] = self::levelFromRow($row);
    }

    return $levels;
  }

  // All levels by status.
  public static function allActiveLevels(): array<Level> {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels WHERE active = 1';
    $results = $db->query($sql);

    $levels = array();
    foreach ($results as $row) {
      $levels[] = self::levelFromRow($row);
    }

    return $levels;
  }

  // All levels by status.
  public static function allActiveBases(): array<Level> {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels WHERE active = 1 AND type = "base"';
    $results = $db->query($sql);

    $bases = array();
    foreach ($results as $row) {
      $bases[] = self::levelFromRow($row);
    }

    return $bases;
  }

  // All levels by type.
  public static function allTypeLevels(string $type): array<Level> {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels WHERE type = ? ORDER BY active DESC';
    $element = array($type);
    $results = $db->query($sql, $element);

    $levels = array();
    foreach ($results as $row) {
      $levels[] = self::levelFromRow($row);
    }

    return $levels;
  }

  // All quiz levels.
  public static function allQuizLevels(): array<Level> {
    return self::allTypeLevels('quiz');
  }

  // All base levels.
  public static function allBaseLevels(): array<Level> {
    return self::allTypeLevels('base');
  }

  // All flag levels.
  public static function allFlagLevels(): array<Level> {
    return self::allTypeLevels('flag');
  }

  // Get a single level.
  public static function getLevel(int $level_id): Level {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels WHERE id = ? LIMIT 1';
    $element = array($level_id);
    $result = $db->query($sql, $element);

    invariant(count($result) === 1, 'Expected exactly one result');
    $level = self::levelFromRow(firstx($result));

    return $level;
  }

  // Check if flag is correct.
  public static function checkAnswer(int $level_id, string $answer): bool {
    $db = self::getDb();

    return
      strtoupper(trim(self::getLevel($level_id)->getFlag())) ===
      strtoupper(trim($answer));
  }

  // Adjust bonus.
  public static function adjustBonus(int $level_id): void {
    $db = self::getDb();

    $sql = 'UPDATE levels SET bonus = GREATEST(bonus - bonus_dec, 0) WHERE id = ? LIMIT 1';
    $element = array($level_id);
    $db->query($sql, $element);
  }

  // Log successful score.
  public static function logValidScore(int $level_id, int $team_id, int $points, string $type): void {
    $db = self::getDb();

    $sql = 'INSERT INTO scores_log (ts, level_id, team_id, points, type) VALUES (NOW(), ?, ?, ?, ?)';
    $elements = array($level_id, $team_id, $points, $type);
    $db->query($sql, $elements);
  }

  // Log base request.
  public static function logBaseEntry(int $level_id, int $code, string $response): void {
    $db = self::getDb();

    $sql = 'INSERT INTO bases_log (ts, level_id, code, response) VALUES (NOW(), ?, ?, ?)';
    $elements = array($level_id, $code, $response);
    $db->query($sql, $elements);
  }

  // Log hint request hint.
  public static function logGetHint(int $level_id, int $team_id, int $penalty): void {
    $db = self::getDb();

    $sql = 'INSERT INTO hints_log (ts, level_id, team_id, penalty) VALUES (NOW(), ?, ?, ?)';
    $elements = array($level_id, $team_id, $penalty);
    $db->query($sql, $elements);
  }

  // Log attempt on score.
  public static function logFailedScore(int $level_id, int $team_id, string $flag): void {
    $db = self::getDb();

    $sql = 'INSERT INTO failures_log (ts, level_id, team_id, flag) VALUES(NOW(), ?, ?, ?)';
    $elements = array($level_id, $team_id, $flag);
    $db->query($sql, $elements);
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

  // Score level. Works for quiz and flags.
  public static function scoreLevel(int $level_id, int $team_id): bool {
    $db = self::getDb();

    // Check if team has already scored this level
    if (self::previousScore($level_id, $team_id, false)) {
      return false;
    }

    $level = self::getLevel($level_id);

    // Calculate points to give
    $points = $level->getPoints() + $level->getBonus();

    // Adjust bonus
    self::adjustBonus($level_id);

    // Score!
    $sql = 'UPDATE teams SET points = points + ?, last_score = NOW() WHERE id = ? LIMIT 1';
    $elements = array($points, $team_id);
    $db->query($sql, $elements);

    // Log the score...
    self::logValidScore($level_id, $team_id, $points, $level->getType());

    return true;
  }

  // Score base.
  public static function scoreBase(int $level_id, int $team_id): bool {
    $db = self::getDb();

    $level = self::getLevel($level_id);

    // Calculate points to give
    if (self::previousScore($level_id, $team_id, false)) {
      $points = $level->getPoints();
    } else {
      $points = $level->getPoints() + $level->getBonus();
    }

    // Score!
    $sql = 'UPDATE teams SET points = points + ?, last_score = NOW() WHERE id = ? LIMIT 1';
    $elements = array($points, $team_id);
    $db->query($sql, $elements);

     // Log the score...
    self::logValidScore($level_id, $team_id, $points, $level->getType());

    return true;
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

  // Get hint.
  public static function getLevelHint(int $level_id, int $team_id): ?string {
    $db = self::getDb();

    $level = self::getLevel($level_id);
    $penalty = $level->getPenalty();

    // Check if team has already gotten this hint or if the team has scored this already
    // If so, hint is free
    if (self::previousHint($level_id, $team_id, false) ||
        self::previousScore($level_id, $team_id, false)) {
      $penalty = 0;
    }

    // Make sure team has enough points to pay
    if (Team::getTeam($team_id)->getPoints() < $penalty) {
      return null;
    }

    // Adjust points
    $sql = 'UPDATE teams SET points = points - ? WHERE id = ? LIMIT 1';
    $elements = array($penalty, $team_id);
    $db->query($sql, $elements);

    // Log the hint
    self::logGetHint($level_id, $team_id, $penalty);

    // Hint!
    return $level->getHint();
  }

  // Get the IP from a base level.
  public static function getBaseIP(int $base_id): string {
    $link = Link::allLinks($base_id)[0];
    $ip = explode(':', $link->getLink())[0];

    return $ip;
  }

  // Request all bases
  public static function getBasesResponses(array<int, string> $bases): array<int, string> {
    // Iterates and request all the bases endpoints for owner
    $responses = array();
    $curl_handlers = array();
    $multi_handler = curl_multi_init();

    // Create the list of request handlers
    foreach ($bases as $base) {
      $base_id = intval(must_have_idx($base, 'id'));
      $base_url = must_have_idx($base, 'url');
      $curl_handlers[$base_id] = curl_init();
      curl_setopt($curl_handlers[$base_id], CURLOPT_URL, $base_url);
      curl_setopt($curl_handlers[$base_id], CURLOPT_HEADER, 0);
      curl_setopt($curl_handlers[$base_id], CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl_handlers[$base_id], CURLOPT_PORT, 12345);
      curl_setopt($curl_handlers[$base_id], CURLOPT_TIMEOUT, 3);
      curl_multi_add_handle($multi_handler, $curl_handlers[$base_id]);
    }

    // Run each request by executing all the handlers
    $running = 0;
    do {
      curl_multi_exec($multi_handler, $running);
    } while ($running > 0);

    // Get responses and remove handlers
    foreach($curl_handlers as $id => $c) {
      $r = array(
        'id' => intval($id),
        'response' => curl_multi_getcontent($c)
      );
      curl_multi_remove_handle($multi_handler, $c);
      array_push($responses, $r);
    }

    curl_multi_close($multi_handler);

    // Return the responses
    return $responses;
  }

  // Bases processing and scoring.
  public static function baseScoring(): void {
    $document_root = must_have_string(Utils::getSERVER(), 'DOCUMENT_ROOT');
    $cmd = 'hhvm -vRepo.Central.Path=/tmp/.hhvm.hhbc_bases '.$document_root.'/scripts/bases.php > /dev/null 2>&1 & echo $!';
    $pid = shell_exec($cmd);
    Control::startScriptLog(intval($pid), 'bases', $cmd);
  }

  // Stop bases processing and scoring process.
  public static function stopBaseScoring(): void {
    // Kill running process
    $pid = Control::getScriptPid('bases');
    if ($pid > 0) {
      exec('kill -9 '.escapeshellarg(strval($pid)));
    }
    // Mark process as stopped
    Control::stopScriptLog($pid);
  }
}