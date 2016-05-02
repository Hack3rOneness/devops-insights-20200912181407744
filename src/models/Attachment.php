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
  public static async function genCreate(
    string $file_param,
    string $filename,
    int $level_id,
  ): Awaitable<bool> {
    $db = await self::genDb();
    $type = '';
    $local_filename = self::attachmentsDir;

    $files = Utils::getFILES();
    $server = Utils::getSERVER();
    // First we put the file in its place
    if ($files->contains($file_param)) {
      $tmp_name = $files[$file_param]['tmp_name'];
      $type = $files[$file_param]['type'];
      $md5_str = md5_file($tmp_name);

      // Extract extension and name
      $parts = explode('.', $filename, 2);
      $local_filename .= firstx($parts).'_'.$md5_str;

      $extension = idx($parts, 1);
      if ($extension !== null) {
        $local_filename .= '.'.$extension;
      }

      // Avoid php shells
      if (ends_with($local_filename, '.php')) {
        $local_filename .= 's'; // Make the extension 'phps'
      }
      move_uploaded_file($tmp_name, must_have_string($server, 'DOCUMENT_ROOT') . $local_filename);
    } else {
      return false;
    }

    // Then database shenanigans
    await $db->queryf(
      'INSERT INTO attachments (filename, type, level_id, created_ts) VALUES (%s, %s, %d, NOW())',
      $local_filename,
      (string)$type,
      $level_id,
    );

    return true;
  }

  // Modify existing attachment.
  public static async function genUpdate(int $id, int $level_id, string $filename): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'UPDATE attachments SET filename = %s, level_id = %d WHERE id = %d LIMIT 1',
      $filename,
      $level_id,
      $id,
    );
  }

  // Delete existing attachment.
  public static async function genDelete(int $attachment_id): Awaitable<void> {
    $db = await self::genDb();
    $server = Utils::getSERVER();

    // Copy file to deleted folder
    $attachment = await self::gen($attachment_id);
    $filename = $attachment->getFilename();
    $parts = pathinfo($filename);
    error_log('Copying from ' . $filename . ' to ' . $parts['dirname'] . '/deleted/' . $parts['basename']);
    $root = strval($server['DOCUMENT_ROOT']);
    $origin = $root . $filename;
    $dest = $root . $parts['dirname'] . '/deleted/' . $parts['basename'];
    copy($origin, $dest);

    // Delete file.
    unlink($origin);

    // Delete from table.
    await $db->queryf(
      'DELETE FROM attachments WHERE id = %d LIMIT 1',
      $attachment_id,
    );
  }

  // Get all attachments for a given level.
  public static async function genAllAttachments(
    int $level_id,
  ): Awaitable<array<Attachment>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM attachments WHERE level_id = %d',
      $level_id,
    );

    $attachments = array();
    foreach ($result->mapRows() as $row) {
      $attachments[] = self::attachmentFromRow($row);
    }

    return $attachments;
  }

  // Get a single attachment.
  public static async function gen(
    int $attachment_id,
  ): Awaitable<Attachment> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM attachments WHERE id = %d LIMIT 1',
      $attachment_id,
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');
    return self::attachmentFromRow($result->mapRows()[0]);
  }

  // Check if a level has attachments.
  public static async function genHasAttachments(int $level_id): Awaitable<bool> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT COUNT(*) FROM attachments WHERE level_id = %d',
      $level_id,
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');
    return intval(idx($result->mapRows()[0], 'COUNT(*)')) > 0;
  }

  private static function attachmentFromRow(Map<string, string> $row): Attachment {
    return new Attachment(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'level_id')),
      must_have_idx($row, 'filename'),
    );
  }
}
