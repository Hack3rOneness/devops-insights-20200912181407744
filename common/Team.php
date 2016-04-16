<?hh // strict

class Team extends Model {
  private function __construct(
    private int $id, 
    private int $active,
    private int $admin,
    private int $protected,
    private int $visible,
    private string $name,
    private string $password_hash,
    private int $points,
    private string $last_score,
    private string $logo,
    private string $created_ts
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getActive(): bool {
    return $this->active === 1;
  }

  public function getAdmin(): bool {
    return $this->admin === 1;
  }

  public function getProtected(): bool {
    return $this->protected === 1;
  }

  public function getVisible(): bool {
    return $this->visible === 1;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getPasswordHash(): string {
    return $this->password_hash;
  }

  public function getPoints(): int {
    return $this->points;
  }

  public function getLastScore(): string {
    return $this->last_score;
  }

  public function getLogo(): string {
    return $this->logo;
  }

  public function getCreatedTs(): string {
    return $this->created_ts;
  }

  private static function teamFromRow(array<string, string> $row): Team {
    return new Team(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'active')),
      intval(must_have_idx($row, 'admin')),
      intval(must_have_idx($row, 'protected')),
      intval(must_have_idx($row, 'visible')),
      must_have_idx($row, 'name'),
      must_have_idx($row, 'password_hash'),
      intval(must_have_idx($row, 'points')),
      must_have_idx($row, 'last_score'),
      must_have_idx($row, 'logo'),
      must_have_idx($row, 'created_ts'),
    );
  }

  // Retrieve how many teams are using one logo.
  public static function whoUses(string $logo): array<Team> {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE logo = ?';
    $element = array($logo);
    $results = $db->query($sql, $element);
    
    $teams = array();
    foreach ($results as $row) {
      $teams[] = self::teamFromRow($row);
    }

    return $teams;
  }
  
  // Generate salted hash.
  public static function generateHash(string $password): string {
    $options = array(
      'cost' => 12,
    );
    return strval(password_hash($password, PASSWORD_DEFAULT, $options));
  }

  // Checks if hash need refreshing.
  public static function regenerateHash(string $password_hash): bool {
    $options = array(
      'cost' => 12,
    );
    return (bool)password_needs_rehash($password_hash, PASSWORD_DEFAULT, $options);
  }

  // Verify if login is valid.
  public static function verifyCredentials(int $team_id, string $password): ?Team {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE id = ? AND (active = 1 OR admin = 1) LIMIT 1';
    $element = array($team_id);
    $results = $db->query($sql, $element);

    if (count($results) > 0) {
      invariant(count($results) === 1, 'Expected exactly one result');
      $team = self::teamFromRow(firstx($results));

      if (password_verify($password, $team->getPasswordHash())) {
        if (self::regenerateHash($team->getPasswordHash())) {
          $new_hash = self::generateHash($password);
          self::updateTeamPassword($new_hash, $team->getId());
        }
        return $team;
      } else {
        return null;
      }
    } else {
      return null;
    }
  }

  // Check to see if the team is active.
  public static function checkTeamStatus(int $team_id): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM teams WHERE id = ? AND active = 1 LIMIT 1';
    $element = array($team_id);
    $results = $db->query($sql, $element);

    if (count($results) > 0) {
      invariant(count($results) === 1, 'Expected exactly one result');
      return (intval(firstx($results)['COUNT(*)']) > 0);
    } else {
      return false;
    }
  }

  // Create a team and return the created team id.
  public static function create(string $name, string $password_hash, string $logo): int {
    $db = self::getDb();

    // Create team
    $sql = 'INSERT INTO teams (name, password_hash, logo, created_ts) VALUES (?, ?, ?, NOW())';
    $elements = array($name, $password_hash, $logo);
    $db->query($sql, $elements);

    // Return newly created team_id
    $sql = 'SELECT id FROM teams WHERE name = ? AND password_hash = ? AND logo = ? LIMIT 1';
    $elements = array($name, $password_hash, $logo);
    $result = $db->query($sql, $elements);

    invariant(count($result) === 1, 'Expected exactly one result');
    return intval(firstx($result)['id']);
  }

  // Add data to a team.
  public static function addTeamData(string $name, string $email, int $team_id): void {
    $db = self::getDb();

    $sql = 'INSERT INTO teams_data (name, email, team_id, created_ts) VALUES (?, ?, ?, NOW())';
    $elements = array($name, $email, $team_id);
    $db->query($sql, $elements);
  }

  // Update team.
  public static function update(string $name, string $logo, int $points, int $team_id): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET name = ?, logo = ? , points = ? WHERE id = ? LIMIT 1';
    $elements = array($name, $logo, $points, $team_id);
    $db->query($sql, $elements);
  }

  // Update team password.
  public static function updateTeamPassword(string $password_hash, int $team_id): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET password_hash = ? WHERE id = ? LIMIT 1';
    $elements = array($password_hash, $team_id);
    $db->query($sql, $elements);
  }

  // Delete team.
  public static function delete(int $team_id): void {
    $db = self::getDb();

    $sql = 'DELETE FROM teams WHERE id = ? AND protected = 0 LIMIT 1';
    $element = array($team_id);
    $db->query($sql, $element);
  }

  // Enable or disable teams by passing 1 or 0.
  public static function setStatus(int $team_id, bool $status): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET active = ? WHERE id = ? LIMIT 1';
    $elements = array($status ? 1 : 0, $team_id);
    $db->query($sql, $elements);
  }

  // Enable or disable all teams by passing 1 or 0.
  public static function setStatusAll(bool $status): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET active = ? WHERE id > 0 AND protected = 0';
    $element = array($status ? 1 : 0);
    $db->query($sql, $element);
  }

  // Sets toggles team admin status.
  public static function setAdmin(int $team_id, bool $admin): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET admin = ? WHERE id = ? AND protected = 0 LIMIT 1';
    $elements = array($admin ? 1 : 0, $team_id);
    $db->query($sql, $elements);
  }

  // Enable or disable team visibility by passing 1 or 0.
  public static function setVisible(int $team_id, bool $visible): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET visible = ? WHERE id = ? LIMIT 1';
    $elements = array($visible ? 1 : 0, $team_id);
    $db->query($sql, $elements);
  }

  // Check if a team name is already created.
  public static function teamExist(string $team_name): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM teams WHERE name = ?';
    $element = array($team_name);
    $result = $db->query($sql, $element);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return (intval(firstx($result)['COUNT(*)']) > 0);
    } else {
      return false;
    }
  }

  // All active teams.
  public static function allActiveTeams(): array<Team> {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE active = 1 ORDER BY id';
    $results = $db->query($sql);

    $teams = array();
    foreach ($results as $row) {
      $teams[] = self::teamFromRow($row);
    }

    return $teams;
  }

  // All visible teams.
  public static function allVisibleTeams(): array<Team> {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE visible = 1 AND active = 1 ORDER BY id';
    $results = $db->query($sql);

    $teams = array();
    foreach ($results as $row) {
      $teams[] = self::teamFromRow($row);
    }

    return $teams;
  }

  // Leaderboard order.
  public static function leaderboard(): array<Team> {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE active = 1 AND visible = 1 ORDER BY points DESC, last_score ASC';
    $results = $db->query($sql);

    $teams = array();
    foreach ($results as $row) {
      $teams[] = self::teamFromRow($row);
    }

    return $teams;
  }

  // Progressive points.
  public static function progressive(string $team_name): mixed {
    $db = self::getDb();

    $sql = 'SELECT * FROM ranking_log WHERE team_name = ? GROUP BY iteration ORDER BY points ASC';
    $element = array($team_name);
    return $db->query($sql, $element);
  }

  // All teams.
  public static function allTeams(): array<Team> {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams ORDER BY points DESC';
    $results = $db->query($sql);

    $teams = array();
    foreach ($results as $row) {
      $teams[] = self::teamFromRow($row);
    }

    return $teams;
  }

  // Get a single team.
  public static function getTeam(int $team_id): Team {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE id = ? LIMIT 1';
    $element = array($team_id);
    $result = $db->query($sql, $element);

    invariant(count($result) === 1, 'Expected exactly one result');
    $team = self::teamFromRow(firstx($result));
    
    return $team;
  }

  // Get a single team, by name.
  public static function getTeamByName(string $team_name): Team {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE name = ? LIMIT 1';
    $elements = array($team_name);
    $result = $db->query($sql, $elements);

    invariant(count($result) === 1, 'Expected exactly one result');
    $team = self::teamFromRow(firstx($result));
    
    return $team;
  }

  // Get points by type.
  public static function pointsByType(int $team_id, string $type): int {
    $db = self::getDb();

    $sql = 'SELECT IFNULL(SUM(points), 0) AS points FROM scores_log WHERE type = ? AND team_id = ?';
    $elements = array($type, $team_id);
    $result = $db->query($sql, $elements);

    invariant(count($result) === 1, 'Expected exactly one result');
    
    return intval(firstx($result)['points']);
  }

  // Get healthy status for points.
  public static function getPointsHealth(int $team_id): bool {
    $db = self::getDb();

    $sql = 'SELECT IFNULL(t.points, 0) AS points, IFNULL(SUM(s.points), 0) AS sum FROM teams AS t, scores_log AS s WHERE t.id = ? AND s.team_id = ?';
    $elements = array($team_id, $team_id);
    $result = $db->query($sql, $elements);

    invariant(count($result) === 1, 'Expected exactly one result');
    $value = firstx($result);
    
    return (intval($value['points']) === intval($value['sum']));
  }

  // Update the last_score field.
  public static function lastScore(int $team_id): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET last_score = NOW() WHERE id = ? LIMIT 1';
    $elements = array($team_id);
    $db->query($sql, $elements);
  }

  // Set all points to zero for all teams.
  public static function resetAllPoints(): void {
    $db = self::getDb();

    $sql = 'UPDATE teams SET points = 0 WHERE id > 0';
    $db->query($sql);
  }

  // Teams total number.
  public static function teamsCount(): int {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) AS count FROM teams';
    $result = $db->query($sql);
    invariant(count($result) === 1, 'Expected exactly one result');
    return intval(firstx($result)['COUNT(*)']);
  }

  public static function completedLevel(int $level_id): array<Team> {
    $db = self::getDb();

    $sql = 'SELECT * FROM teams WHERE id IN (SELECT team_id FROM scores_log WHERE level_id = ? ORDER BY ts) AND visible = 1 AND active = 1';
    $element = array($level_id);
    $results = $db->query($sql, $element);

    $teams = array();
    foreach ($results as $row) {
      $teams[] = self::teamFromRow($row);
    }

    return $teams;
  }

  // Get rank position for a team
  public static function myRank(int $team_id): int {
    $rank = 1;
    foreach (self::leaderboard() as $team) {
      if ($team_id === $team->getId()) {
        return $rank;
      }
      $rank++;
    }

    return $rank;
  }
}
