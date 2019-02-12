<?hh

class PasswordTypesTest extends FBCTFTest {

  public function testAll(): void {
    $all = HH\Asio\join(Configuration::genAllPasswordTypes());
    $this->assertEquals(1, count($all));

    $p = $all[0];
    $this->assertTrue((bool)preg_match($p->getValue(), 'Pas$word1234'));
  }
}
