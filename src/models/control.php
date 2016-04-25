<?hh

class Control extends Model {
  public static function allTokens() {
    $db = self::getDb();
    $sql = 'SELECT * FROM registration_tokens';
    return $db->query($sql);
  }

  public static function allAvailableTokens() {
    $db = self::getDb();
    $sql = 'SELECT * FROM registration_tokens WHERE used = 0';
    return $db->query($sql);
  }

  public static function checkToken(string $token): bool {
    $db = self::getDb();
    $sql = 'SELECT COUNT(*) FROM registration_tokens WHERE used = 0 AND token = ?';
    $element = array($token);
    return intval(must_have_idx(firstx($db->query($sql, $element)), 'COUNT(*)')) > 0;
  }

  public static function useToken(string $token, int $team_id): void {
    $db = self::getDb();
    $sql = 'UPDATE registration_tokens SET used = 1, team_id = ?, use_ts = NOW() WHERE token = ? LIMIT 1';
    $elements = array($team_id, $token);
    $db->query($sql, $elements);
  }

  public static function deleteToken(string $token): void {
    $db = self::getDb();
    $sql = 'DELETE from registration_tokens WHERE token = ? LIMIT 1';
    $element = array($token);
    $db->query($sql, $element);
  }

  public static function startScriptLog(int $pid, string $name, string $cmd): void {
    $db = self::getDb();
    $sql = 'INSERT INTO scripts (ts, pid, name, cmd, status) VALUES (NOW(), ?, ?, ?, 1)';
    $elements = array($pid, $name, $cmd);
    $db->query($sql, $elements);
  }

  public static function stopScriptLog(int $pid): void {
    $db = self::getDb();
    $sql = 'UPDATE scripts SET status = 0 WHERE pid = ? LIMIT 1';
    $element = array($pid);
    $db->query($sql, $element);
  }

  public static function getScriptPid(string $name): int {
    $db = self::getDb();
    $sql = 'SELECT pid FROM scripts WHERE name = ? AND status = 1 LIMIT 1';
    $element = array($name);
    return intval(must_have_idx(firstx($db->query($sql, $element)), 'pid'));
  }

  public static function clearScriptLog(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM scripts WHERE id > 0 AND status = 0';
    $db->query($sql);
  }

  public static function createTokens(): void {
    $db = self::getDb();
    $crypto_strong = True;
    $tokens = array();
    $query = array();
    $token_len = 15;
    $token_number = 50;
    for ($i = 0; $i < $token_number; $i++) {
      $token = md5(
        base64_encode(
          openssl_random_pseudo_bytes(
            $token_len,
            $crypto_strong,
          )
        )
      );
      $sql = 'INSERT INTO registration_tokens (token, created_ts) VALUES (?, NOW())';
      $element = array($token);
      $db->query($sql, $element);
    }
  }

  public static function exportTokens(): void {
    $db = self::getDb();
    $sql = 'SELECT * FROM registration_tokens WHERE used = 0';
    $tokens = $db->query($sql);
    // TODO
  }

  public static function begin(): void {
    $db = self::getDb();
    // Disable registration
    Configuration::update('registration', '0');

    // Reset all points
    Team::resetAllPoints();

    // Clear scores log
    self::resetScores();

    // Clear hints log
    self::resetHints();

    // Clear failures log
    self::resetFailures();

    // Clear bases log
    self::resetBases();
    self::clearScriptLog();

    // Mark game as started
    Configuration::update('game', '1');

    // Enable scoring
    Configuration::update('scoring', '1');

    // Take timestamp of start
    $start_ts = time();
    Configuration::update('start_ts', strval($start_ts));

    // Calculate timestamp of the end
    $duration = intval(Configuration::get('game_duration')->getValue());
    $end_ts = $start_ts + $duration;
    Configuration::update('end_ts', strval($end_ts));

    // Kick off timer
    Configuration::update('timer', '1');

    // Reset and kick off progressive scoreboard
    Progressive::reset();
    Progressive::run();

    // Kick off scoring for bases
    Level::baseScoring();
  }

  public static function end(): void {
    // Mark game as finished and it stops progressive scoreboard
    Configuration::update('game', '0');

    // Disable scoring
    Configuration::update('scoring', '0');

    // Put timestampts to zero
    Configuration::update('start_ts', '0');
    Configuration::update('end_ts', '0');

    // Stop timer
    Configuration::update('timer', '0');

    // Stop bases scoring process
    Level::stopBaseScoring();

    // Stop progressive scoreboard process
    Progressive::stop();
  }

  public static function backupDb(): void {
    // TODO
  }

  public static function newAnnouncement(string $announcement): void {
    $db = self::getDb();
    $sql = 'INSERT INTO announcements_log (ts, announcement) (SELECT NOW(), ?) LIMIT 1';
    $element = array($announcement);
    $db->query($sql, $element);
  }

  public static function deleteAnnouncement(int $announcement_id): void {
    $db = self::getDb();
    $sql = 'DELETE FROM announcements_log WHERE id = ? LIMIT 1';
    $element = array($announcement_id);
    $db->query($sql, $element);
  }

  public static function allAnnouncements() {
    $db = self::getDb();
    $sql = 'SELECT * FROM announcements_log ORDER BY ts DESC';
    return $db->query($sql);
  }

  public static function allActivity() {
    $db = self::getDb();
    $sql = 'SELECT DATE_FORMAT(scores_log.ts, "%H:%i:%S") AS time, teams.name AS team, countries.name AS country, scores_log.team_id AS team_id FROM scores_log, levels, teams, countries WHERE scores_log.level_id = levels.id AND levels.entity_id = countries.id AND scores_log.team_id = teams.id AND teams.visible = 1 ORDER BY time ASC';
    return $db->query($sql);
  }

  public static function resetScores(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM scores_log WHERE id > 0';
    $db->query($sql);
  }

  public static function resetHints(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM hints_log WHERE id > 0';
    $db->query($sql);
  }

  public static function resetFailures(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM failures_log WHERE id > 0';
    $db->query($sql);
  }

  public static function resetBases(): void {
    $db = self::getDb();
    $sql = 'DELETE FROM bases_log WHERE id > 0';
    $db->query($sql);
  }
}
