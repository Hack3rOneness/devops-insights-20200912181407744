<?hh

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="links2")
 **/
class Links2 {
  /** @Id @Column(type="integer") @GeneratedValue **/
  protected int $id;
  /** @Column(type="string") **/
  protected string $link;
  /** @Column(type="integer") **/
  protected int $levelId;
  /** @Column(type="datetime", nullable=false) **/ 
  protected int $createdTs;

  public function getId()
  {
    return $this->id;
  }

  public function getLink() {
    return $this->link;
  }

  public function setLink(string $link)
  {
    $this->link = $link;
  }
  public function getLevelId()
  {
    return $this->levelId;
  }

  public function setLevelId(int $levelId)
  {
    $this->levelId = $levelId;
  }
  public function getCreatedTs()
  {
    return $this->createdTs;
  }

  public function setCreatedTs($createdTs)
  {
    $this->createdTs = $createdTs;
  }
}
