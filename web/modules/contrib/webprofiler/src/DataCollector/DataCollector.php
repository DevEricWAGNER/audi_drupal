<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\VarDumper\Caster\CutStub;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;

/**
 * An abstract DataCollector that supports dependency serialization.
 *
 * Most of this code is copied from
 * Symfony\Component\HttpKernel\DataCollector\DataCollector.php, but without the
 * __sleep() and __wakeup() methods, that are replaced by the
 * DependencySerializationTrait.
 */
abstract class DataCollector implements DataCollectorInterface {

  use DependencySerializationTrait;

  /**
   * @var array|Data
   */
  protected array|Data $data = [];

  /**
   * Converts the variable into a serializable Data instance.
   *
   * This array can be displayed in the template using
   * the VarDumper component.
   */
  protected function cloneVar(mixed $var): Data {
    if ($var instanceof Data) {
      return $var;
    }

    $cloner = new VarCloner();
    $cloner->setMaxItems(-1);
    $cloner->addCasters($this->getCasters());

    return $cloner->cloneVar($var);
  }

  /**
   * @return callable[]
   *   The casters to add to the cloner.
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
