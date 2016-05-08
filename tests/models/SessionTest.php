<?hh

class SessionTest extends FBCTFTest {

  public function testAll(): void {
    $all = HH\Asio\join(Session::genAllSessions());
    $this->assertEquals(1, count($all));

    $a = $all[0];
    $this->assertEquals(1, $a->getId());
    $this->assertEquals('cookie', $a->getCookie());
    $this->assertEquals('data', $a->getData());
    $this->assertEquals(1, $a->getTeamId());
  }

  public function testCreate(): void {
    HH\Asio\join(Session::genCreate('cookie 2', 'data 2'));
    $all = HH\Asio\join(Session::genAllSessions());
    $this->assertEquals(2, count($all));

    $a = $all[0];
    $this->assertEquals(2, $a->getId());
    $this->assertEquals('cookie 2', $a->getCookie());
    $this->assertEquals('data 2', $a->getData());
    $this->assertEquals(1, $a->getTeamId());
  }
}
