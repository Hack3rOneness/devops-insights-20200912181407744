<?hh // strict

class Announcement extends Model {
  private function __construct(
    private int $id,
    private string $announcement,
    private string $ts
    ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getAnnouncement(): string {
    return $this->announcement;
  }

  public function getTs(): string {
    return $this->ts;
  }

  private static function announcementFromRow(array<string, string> $row): Announcement {
    return new Announcement(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'announcement'),
      must_have_idx($row, 'ts'),
    );
  }

  public static function create(string $announcement): void {
    $db = self::getDb();
    $sql = 'INSERT INTO announcements_log (ts, announcement) (SELECT NOW(), ?) LIMIT 1';
    $element = array($announcement);
    $db->query($sql, $element);
  }

  public static function delete(int $announcement_id): void {
    $db = self::getDb();
    $sql = 'DELETE FROM announcements_log WHERE id = ? LIMIT 1';
    $element = array($announcement_id);
    $db->query($sql, $element);
  }

  // Get all tokens.
  public static function allAnnouncements(): array<Announcement> {
    $db = self::getDb();
    $sql = 'SELECT * FROM announcements_log ORDER BY ts DESC';
    $results = $db->query($sql);

    $announcements = array();
    foreach ($results as $row) {
      $announcements[] = self::announcementFromRow($row);
    }

    return $announcements;
  }
}
