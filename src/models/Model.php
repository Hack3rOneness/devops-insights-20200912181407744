<?hh // strict

abstract class Model {
  protected static Db $db = MUST_MODIFY;
  protected static MCRouter $mc = MUST_MODIFY;

  protected static async function genDb(): Awaitable<AsyncMysqlConnection> {
    if (self::$db === MUST_MODIFY) {
      self::$db = Db::getInstance();
    }
    return await self::$db->genConnection();
  }

  /**
   * @codeCoverageIgnore
   */
  protected static function getMc(): MCRouter {
    if (self::$mc === MUST_MODIFY) {
      $config = parse_ini_file('../../settings.ini');
      $host = must_have_idx($config, 'MC_HOST');
      $port = must_have_idx($config, 'MC_PORT');
      self::$mc = MCRouter::createSimple(
        Vector {$host.':'.$port},
      );
    }
    return self::$mc;
  }
}
