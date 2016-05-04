<?hh // strict

abstract class Model {
  protected static Db $db = MUST_MODIFY;

  protected static async function genDb(): Awaitable<AsyncMysqlConnection> {
    if (self::$db === MUST_MODIFY) {
      self::$db = Db::getInstance();
    }
    return await self::$db->genConnection();
  }
}
