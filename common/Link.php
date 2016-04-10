<?hh // strict

class Link {
  /* HH_FIXME[4055] */
  private static DB $db;

  public function __construct(private int $id, private int $levelId, private string $link) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getLevelId(): int {
    return $this->levelId;
  }

  public function getLink(): string {
    return $this->link;
  }

  public static function getDb(): DB {
    if (self::$db === null) {
      self::$db = DB::getInstance();
    }
    if (!self::$db->isConnected()) {
      self::$db->connect();
    }
    return self::$db;
  }

  // Create link for a given level.
  public static function create(string $link, int $level_id): void {
    $db = self::getDb();
    $sql = 'INSERT INTO links (link, level_id, created_ts) VALUES (?, ?, NOW())';
    $elements = array($link, $level_id);
    $db->query($sql, $elements);
  }

  // Modify existing link.
  public static function update(string $link, int $level_id, int $link_id): void {
    $db = self::getDb();
    $sql = 'UPDATE links SET link = ?, level_id = ? WHERE id = ? LIMIT 1';
    $elements = array($link, $level_id, $link_id);
    $db->query($sql, $elements);
  }

  // Delete existing link.
  public static function delete(int $link_id): void {
    $db = self::getDb();
    $sql = 'DELETE FROM links WHERE id = ? LIMIT 1';
    $element = array($link_id);
    $db->query($sql, $element);
  }

  // Get all links for a given level.
  public static function allLinks(int $level_id): array<Link> {
    $db = self::getDb();
    $sql = 'SELECT * FROM links WHERE level_id = ?';
    $element = array($level_id);
    $results = $db->query($sql, $element);

    $links = array();
    foreach ($results as $row) {
      $links[] = self::linkFromRow($row);
    }

    return $links;
  }

  private static function linkFromRow(array<string, string> $row): Link {
    return new Link(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'level_id')),
      must_have_idx($row, 'link'),
    );
  }

  // Get a single link.
  public static function get(int $link_id): Link {
    $db = self::getDb();
    $sql = 'SELECT * FROM links WHERE id = ? LIMIT 1';
    $element = array($link_id);

    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return self::linkFromRow(firstx($results));
  }

  // Check if a level has links.
  public static function hasLinks(int $level_id): bool {
    $db = self::getDb();
    $sql = 'SELECT COUNT(*) FROM links WHERE level_id = ?';
    $element = array($level_id);

    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['COUNT(*)']) > 0;
  }
}
