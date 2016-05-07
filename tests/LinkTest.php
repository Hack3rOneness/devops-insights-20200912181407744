<?hh

class LinkTest extends FBCTFTest {

  public function testAllLinks(): void {
    $all = HH\Asio\join(Link::genAllLinks(1));
    $this->assertEquals(1, count($all));

    $l = $all[0];
    $this->assertEquals(1, $l->getId());
    $this->assertEquals(1, $l->getLevelId());
    $this->assertEquals('link', $l->getLink());
  }

}
