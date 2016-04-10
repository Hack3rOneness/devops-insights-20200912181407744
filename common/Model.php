<?hh // strict

abstract class Model {
  protected static DB $db = MUST_MODIFY;

  protected static function getDb(): DB {
    if (self::$db === MUST_MODIFY) {
      self::$db = DB::getInstance();
    }
    if (!self::$db->isConnected()) {
      self::$db->connect();
    }
    return self::$db;
  }
}