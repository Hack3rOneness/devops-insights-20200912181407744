<?hh // strict

class Announcement extends Model {

  const string MC_KEY = 'all_announcements:';

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

  public static async function genCreate(string $announcement): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO announcements_log (ts, announcement) (SELECT NOW(), %s) LIMIT 1',
      $announcement,
    );

    self::getMc()->delete(self::MC_KEY);
  }

  public static async function genDelete(int $announcement_id): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'DELETE FROM announcements_log WHERE id = %d LIMIT 1',
      $announcement_id,
    );

    self::getMc()->delete(self::MC_KEY);
  }

  // Get all tokens.
  public static async function genAllAnnouncements(): Awaitable<array<Announcement>> {
    $mc = self::getMc();
    $mc_result = $mc->get(self::MC_KEY);

    if ($mc_result) {
      $rows = $mc_result;
    } else {
      $db = await self::genDb();
      $db_result = await $db->queryf(
        'SELECT * FROM announcements_log ORDER BY ts DESC',
      );
      $rows = array_map(
        $map ==> $map->toArray(),
        $db_result->mapRows(),
      );
      $mc->set(self::MC_KEY, $rows);
    }

    $announcements = array();
    foreach ($rows as $row) {
      $announcements[] = self::announcementFromRow($row);
    }

    return $announcements;
  }
}
