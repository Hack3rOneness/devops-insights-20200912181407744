<?hh

class AttachmentTest extends FBCTFTest {

  public function testAllAttachments(): void {
    $all = HH\Asio\join(Attachment::genAllAttachments(1));
    $this->assertEquals(1, count($all));

    $l = $all[0];
    $this->assertEquals(1, $l->getId());
    $this->assertEquals(1, $l->getLevelId());
    $this->assertEquals('link', $l->getFilename());
  }

  public function testCreateAttachment(): void {
    HH\Asio\join(Attachment::genCreate("link 2", 1));

    $all = HH\Asio\join(Attachment::genAllAttachments(1));

    $this->assertEquals(2, count($all));

    $l = $all[1];
    $this->assertEquals(2, $l->getId());
    $this->assertEquals(1, $l->getLevelId());
    $this->assertEquals('link 2', $l->getAttachment());

    $l = $all[0];
    $this->assertEquals(1, $l->getId());
    $this->assertEquals(1, $l->getLevelId());
    $this->assertEquals('link', $l->getAttachment());
  }

  public function testUpdateAttachment(): void {
    HH\Asio\join(Attachment::genUpdate("link updated", 1, 1));

    $all = HH\Asio\join(Attachment::genAllAttachments(1));

    $this->assertEquals(1, count($all));

    $l = $all[0];
    $this->assertEquals(1, $l->getId());
    $this->assertEquals(1, $l->getLevelId());
    $this->assertEquals('link updated', $l->getAttachment());
  }

  public function testDeleteAttachment(): void {
    HH\Asio\join(Attachment::genDelete(1));

    $all = HH\Asio\join(Attachment::genAllAttachments(1));

    $this->assertEquals(0, count($all));
  }

  public function testGenAttachment(): void {
    $l = HH\Asio\join(Attachment::gen(1));

    $this->assertEquals(1, $l->getId());
    $this->assertEquals(1, $l->getLevelId());
    $this->assertEquals('link', $l->getAttachment());
  }

  public function testHasAttachments(): void {
    $this->assertTrue(HH\Asio\join(Attachment::genHasAttachments(1)));
    $this->assertFalse(HH\Asio\join(Attachment::genHasAttachments(2)));
  }
}
