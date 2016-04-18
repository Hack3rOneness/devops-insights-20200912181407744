<?hh // strict

class Attachment extends Model {
  // TODO: Configure this
  const string attachmentsDir = '/data/attachments/';

  private function __construct(private int $id, private int $levelId, private string $filename) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getFilename(): string {
    return $this->filename;
  }

  public function getLevelId(): int {
    return $this->levelId;
  }

  // Create attachment for a given level.
  public static function create(string $file_param, string $filename, int $level_id): bool {
    $db = self::getDb();
    $type = '';
    $local_filename = self::attachmentsDir;

    $files = getFILES();
    $server = getSERVER();
    // First we put the file in its place
    if ($files->contains($file_param)) {
      $tmp_name = $files[$file_param]['tmp_name'];
      $type = $files[$file_param]['type'];
      $md5_str = md5_file($tmp_name);

      // Extract extension and name
      $parts = pathinfo($filename);

      // Avoid php shells
      if ($parts['extension'] === 'php') {
        $local_filename .= $parts['filename'] . '_' . $md5_str . '.' . $parts['extension'] . '.txt';
      } else {
        $local_filename .= $parts['filename'] . '_' . $md5_str . '.' . $parts['extension'];
      }
      move_uploaded_file($tmp_name, strval($server['DOCUMENT_ROOT']) . $local_filename);
    } else {
      return false;
    }

    // Then database shenanigans
    $sql = 'INSERT INTO attachments (filename, type, level_id, created_ts) VALUES (?, ?, ?, NOW())';
    $elements = array($local_filename, $type, $level_id);
    $db->query($sql, $elements);
    return true;
  }

  // Modify existing attachment.
  public static function update(int $id, int $level_id, string $filename): void {
    $db = self::getDb();
    $sql = 'UPDATE attachments SET filename = ?, level_id = ? WHERE id = ? LIMIT 1';
    $elements = array($filename, $level_id, $id);
    $db->query($sql, $elements);
  }

  // Delete existing attachment.
  public static function delete(int $attachment_id): void {
    $db = self::getDb();
    $server = getSERVER();

    // Copy file to deleted folder
    $filename = self::get($attachment_id)->getFilename();
    $parts = pathinfo($filename);
    error_log('Copying from ' . $filename . ' to ' . $parts['dirname'] . '/deleted/' . $parts['basename']);
    $root = strval($server['DOCUMENT_ROOT']);
    $origin = $root . $filename;
    $dest = $root . $parts['dirname'] . '/deleted/' . $parts['basename'];
    copy($origin, $dest);

    // Delete file.
    unlink($origin);

    // Delete from table.
    $sql = 'DELETE FROM attachments WHERE id = ? LIMIT 1';
    $element = array($attachment_id);
    $db->query($sql, $element);
  }

  // Get all attachments for a given level.
  public static function allAttachments(int $level_id): array<Attachment> {
    $db = self::getDb();
    $sql = 'SELECT * FROM attachments WHERE level_id = ?';
    $element = array($level_id);
    $results = $db->query($sql, $element);

    $attachments = array();
    foreach ($results as $row) {
      $attachments[] = self::attachmentFromRow($row);
    }

    return $attachments;
  }

  // Get a single attachment.
  public static function get(int $attachment_id): Attachment {
    $db = self::getDb();
    $sql = 'SELECT * FROM attachments WHERE id = ? LIMIT 1';
    $element = array($attachment_id);
    $results = $db->query($sql, $element);

    invariant(count($results) === 1, 'Expected exactly one result');
    return self::attachmentFromRow(firstx($results));
  }

  private static function attachmentFromRow(array<string, string> $row): Attachment {
    return new Attachment(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'level_id')),
      must_have_idx($row, 'filename'),
    );
  }

  // Check if a level has attachments.
  public static function hasAttachments(int $level_id): bool {
    $db = self::getDb();
    $sql = 'SELECT COUNT(*) FROM attachments WHERE level_id = ?';
    $element = array($level_id);

    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['COUNT(*)']) > 0;
  }
}
