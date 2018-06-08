<?php namespace security\credentials\unittest;

use security\credentials\FromFile;
use io\File;
use io\TempFile;
use io\streams\Streams;
use io\streams\MemoryInputStream;

class FromFileTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture() {
    return new FromFile(Streams::readableFd(new MemoryInputStream(
      "TEST_DB_PASSWORD=db\n".
      "TEST_LDAP_PASSWORD=ldap\n".
      "PROD_MASTER_KEY=master\n".
      "XP/APP/MYSQL=test\n".
      "CLOUD_SECRET=!\\u{0007}nicode"
    )));
  }

  #[@test, @values([
  #  [new File("filename"), "files"],
  #  ["filename", "filenames"]
  #])]
  public function can_create($arg, $from) {
    new FromFile($arg);
  }

  #[@test]
  public function file_kept_by_default() {
    $file= new TempFile($this->name);
    $fixture= new FromFile($file);
    $fixture->open();
    $fixture->close();
    $this->assertTrue($file->exists());
  }

  #[@test]
  public function can_optionally_be_removed_after_close() {
    $file= new TempFile($this->name);
    $fixture= new FromFile($file, FromFile::REMOVE);
    $fixture->open();
    $fixture->close();
    $this->assertFalse($file->exists());
  }

  #[@test]
  public function unicode_escape_sequences() {
    $this->assertCredential("!\x07nicode", 'cloud_secret');
  }
}