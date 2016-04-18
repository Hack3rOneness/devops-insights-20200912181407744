<?hh // strict

abstract class Model {
  protected static Db $db = MUST_MODIFY;

  protected static function getDb(): Db {
    if (self::$db === MUST_MODIFY) {
      self::$db = Db::getInstance();
    }
    if (!self::$db->isConnected()) {
      self::$db->connect();
    }
    return self::$db;
  }
}