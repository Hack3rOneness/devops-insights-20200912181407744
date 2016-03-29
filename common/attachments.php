<?hh

class Attachments {
  private DB $db;

  // TODO: Configure this
  private string $attachments_dir = '/data/attachments/';

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  // Create attachment for a given level.
  public function create($file_param, $filename, $level_id): bool {
    $type = '';
    $local_filename =  $this->attachments_dir;

    // First we put the file in its place
    if (isset($_FILES[$file_param])) {
      $tmp_name = $_FILES[$file_param]['tmp_name'];
      $type = $_FILES[$file_param]['type'];
      $md5_str = md5_file($tmp_name);
      // Avoid php shells
      if (strpos($filename, '.php') !== false) {
        $local_filename .= md5($md5_str.$level_id) . '_' . $filename . '.txt';
      } else {
        $local_filename .= md5($md5_str.$level_id) . '_' . $filename;
      }
      move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT'] . $local_filename);
    } else {
      return false;
    }

    // Then database shenanigans
    $sql = 'INSERT INTO attachments (filename, type, level_id, created_ts) VALUES (?, ?, ?, NOW())';
    $elements = array($local_filename, $type, $level_id);
    $this->db->query($sql, $elements);
    return true;
  }

  // Modify existing attachment.
  public function update($filename, $level_id, $attachment_id): void {
    $sql = 'UPDATE attachments SET filename = ?, level_id = ? WHERE id = ? LIMIT 1';
    $elements = array($filename, $level_id, $attachment_id);
    $this->db->query($sql, $elements);
  }

  // Delete existing attachment.
  public function delete($attachment_id): void {
    $sql = 'DELETE FROM attachments WHERE id = ? LIMIT 1';
    $element = array($attachment_id);
    $this->db->query($sql, $element);
  }

  // Get all attachments for a given level.
  public function all_attachments($level_id) {
    $sql = 'SELECT * FROM attachments WHERE level_id = ?';
    $element = array($level_id);
    return $this->db->query($sql, $element);
  }

  // Get a single attachment.
  public function get_attachment($attachment_id) {
    $sql = 'SELECT * FROM attachments WHERE id = ? LIMIT 1';
    $element = array($attachment_id);
    return $this->db->query($sql, $element);
  }

  // Check if a level has attachments.
  public function has_attachments($level_id): bool {
    $sql = 'SELECT COUNT(*) FROM attachments WHERE level_id = ?';
    $element = array($level_id);
    $attachment = $this->db->query($sql, $element);
    if ($attachment) {
      return (bool)$attachment[0]['COUNT(*)'];
    } else {
      return false;
    }
  }
}
