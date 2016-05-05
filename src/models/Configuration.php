<?hh // strict

class Configuration extends Model {
  private function __construct(private int $id, private string $field, private string $value, private string $description) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getField(): string {
    return $this->field;
  }

  public function getValue(): string {
    return $this->value;
  }

  public function getDescription(): string {
    return $this->description;
  }

  // Create configuration entry.
  public static async function genCreate(
    string $field,
    string $value,
    string $description,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO configuration (field, value, description) VALUES(%s, %s, %s) LIMIT 1',
      $field,
      $value,
      $description,
    );
  }

  // Get configuration entry.
  public static async function gen(
    string $field,
  ): Awaitable<Configuration> {
    $mc = self::getMc();
    $key = 'configuration:' . $field;
    $mc_result = await \HH\Asio\wrap($mc->get($key));
    if ($mc_result->isSucceeded()) {
      $result = json_decode($mc_result->getResult(), true);
    } else {
      $db = await self::genDb();
      $db_result = await $db->queryf(
        'SELECT * FROM configuration WHERE field = %s LIMIT 1',
        $field,
      );
      invariant($db_result->numRows() === 1, 'Expected exactly one result');
      $result = firstx($db_result->mapRows())->toArray();
      await $mc->set($key, json_encode($result));
    }
    return self::configurationFromRow($result);
  }

  // Change configuration field.
  public static async function genUpdate(
    string $field,
    string $value,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'UPDATE configuration SET value = %s WHERE field = %s LIMIT 1',
      $value,
      $field,
    );

    await self::getMc()->del('configuration:' . $field);
  }

  // Check if field is valid.
  public static async function genValidField(
    string $field,
  ): Awaitable<bool> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT COUNT(*) FROM configuration WHERE field = %s',
      $field,
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');

    return intval(idx(firstx($result->mapRows()), 'COUNT(*)')) > 0;
  }

  // All the configuration.
  public static async function genAllConfiguration(
  ): Awaitable<array<Configuration>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM configuration',
    );

    $configuration = array();
    foreach ($result->mapRows() as $row) {
      $configuration[] = self::configurationFromRow($row->toArray());
    }

    return $configuration;
  }

  private static function configurationFromRow(array<string, string> $row): Configuration {
    return new Configuration(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'field'),
      must_have_idx($row, 'value'),
      must_have_idx($row, 'description'),
    );
  }
}
