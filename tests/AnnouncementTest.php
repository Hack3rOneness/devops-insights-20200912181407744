<?hh

class AnnouncementTest extends FBCTFTest {

    public function testAllAnnouncements(): void {
    $all = HH\Asio\join(Announcement::genAllAnnouncements());
    $this->assertEquals(2, count($all));

    $first = $all[1];
    $this->assertEquals(1, $first->getId());
    $this->assertEquals('Hello buddy!', $first->getAnnouncement());
    $this->assertEquals('2016-04-24 17:15:23', $first->getTs());
  }

  public function testCreateAnnouncent(): void {
    HH\Asio\join(Announcement::genCreate('New'));
    $all = HH\Asio\join(Announcement::genAllAnnouncements());
    $this->assertEquals(3, count($all));

    $first = $all[0];
    $this->assertEquals(3, $first->getId());
    $this->assertEquals('New', $first->getAnnouncement());
  }

  public function testDeleteAnnouncement(): void {
    HH\Asio\join(Announcement::genDelete(1));
    $all = HH\Asio\join(Announcement::genAllAnnouncements());
    $this->assertEquals(1, count($all));

    $first = $all[0];
    $this->assertEquals(2, $first->getId());
    $this->assertEquals('I like it!', $first->getAnnouncement());
    $this->assertEquals('2016-04-26 12:14:20', $first->getTs());
  }
}
