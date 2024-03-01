<?php

declare(strict_types = 1);

namespace Drupal\webprofiler;

use Symfony\Component\VarDumper\Caster\CutStub;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Trait with common code to dump variables.
 */
trait DumpTrait {

  /**
   * Clone a variable into a Data object.
   *
   * @var \Symfony\Component\VarDumper\Cloner\AbstractCloner
   */
  protected AbstractCloner $cloner;

  /**
   * Internal resource to store dumped data.
   *
   * @var resource
   */
  private $output;

  /**
   * Dump a Data object.
   *
   * @param \Symfony\Component\VarDumper\Cloner\Data $data
   *   The data to dump.
   * @param int $maxDepth
   *   The maximum depth to dump.
   *
   * @return array|string
   *   The dumped data.
   */
  public function dumpData(Data $data, int $maxDepth = 0): array|string {
    $dumper = new HtmlDumper();
    $dumper->setOutput($this->output = fopen('php://memory', 'r+b'));
    $dumper->setTheme('light');

    $file_link_formatter = \Drupal::service('webprofiler.file_link_formatter');
    $dumper->setDisplayOptions(['fileLinkFormat' => $file_link_formatter]);

    $dumper->dump($data, NULL, [
      'maxDepth' => $maxDepth,
    ]);

    $dump = stream_get_contents($this->output, -1, 0);
    rewind($this->output);
    ftruncate($this->output, 0);

    return str_replace("\n</pre", '</pre', rtrim($dump));
  }

  /**
   * Convert a variable to a Data object.
   *
   * @param mixed $var
   *   The variable to convert.
   *
   * @return \Symfony\Component\VarDumper\Cloner\Data
   *   The converted variable.
   */
  protected function cloneVar(mixed $var): Data {
    if ($var instanceof Data) {
      return $var;
    }
    if (!isset($this->cloner)) {
      $this->cloner = new VarCloner();
      $this->cloner->setMaxItems(-1);
      $this->cloner->addCasters($this->getCasters());
    }

    return $this->cloner->cloneVar($var);
  }

  /**
   * Return a list of casters.
   *
   * @return callable[]
   *   The list of casters.
   */
  protected function getCasters(): array {
    return [
      '*' => function ($v, array $a, Stub $s, $isNested) {
        if (!$v instanceof Stub) {
          foreach ($a as $k => $v2) {
            if (\is_object($v2) && !$v2 instanceof \DateTimeInterface && !$v2 instanceof Stub) {
              $a[$k] = new CutStub($v2);
            }
          }
        }

          return $a;
      },
    ] + ReflectionCaster::UNSET_CLOSURE_FILE_INFO;
  }

}
