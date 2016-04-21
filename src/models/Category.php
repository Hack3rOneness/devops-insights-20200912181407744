<?hh // strict

class Category extends Model {
  private function __construct(
    private int $id,
    private string $category,
    private string $created_ts
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getCategory(): string {
    return $this->category;
  }

  public function getCreatedTs(): string {
    return $this->created_ts;
  }

  private static function categoryFromRow(array<string, string> $row): Category {
    return new Category(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'category'),
      must_have_idx($row, 'created_ts'),
    );
  }

  // All categories.
  public static function allCategories(): array<Category> {
    $db = self::getDb();

    $sql = 'SELECT * FROM categories';
    $results = $db->query($sql);

    $categories = array();
    foreach ($results as $row) {
      $categories[] = self::categoryFromRow($row);
    }

    return $categories;
  }

  // Check if category is used.
  public static function isUsed(int $category_id): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM levels WHERE category_id = ?';
    $element = array($category_id);
    $result = $db->query($sql, $element);

    if (count($result) > 0) {
      invariant(count($result) === 1, 'Expected exactly one result');
      return intval(firstx($result)['COUNT(*)']) > 0;
    } else {
      return false;
    }
  }

  // Delete category.
  public static function delete(int $category_id): void {
    $db = self::getDb();

    $sql = 'DELETE FROM categories WHERE id = ? AND id NOT IN (SELECT category_id FROM levels) LIMIT 1';
    $elements = array($category_id);
    $db->query($sql, $elements);
  }

  // Create category.
  public static function create(string $category): int {
    $db = self::getDb();

    // Create category
    $sql = 'INSERT INTO categories (category, created_ts) VALUES (?, NOW())';
    $element = array($category);
    $db->query($sql, $element);

    // Return newly created category_id
    $sql = 'SELECT id FROM categories WHERE category = ? LIMIT 1';
    $element = array($category);
    $result = $db->query($sql, $element);

    invariant(count($result) === 1, 'Expected exactly one result');
    return intval(firstx($result)['id']);
  }

  // Update category.
  public static function update(string $category, int $category_id): void {
    $db = self::getDb();

    $sql = 'UPDATE categories SET category = ? WHERE id = ? LIMIT 1';
    $elements = array($category, $category_id);
    $db->query($sql, $elements);
  }

  // Get category.
  public static function getSingleCategory(int $category_id): Category {
    $db = self::getDb();

    $sql = 'SELECT * FROM categories WHERE id = ? LIMIT 1';
    $element = array($category_id);
    $result = $db->query($sql, $element);

    invariant(count($result) === 1, 'Expected exactly one result');
    $category = self::categoryFromRow(firstx($result));

    return $category;
  }
}