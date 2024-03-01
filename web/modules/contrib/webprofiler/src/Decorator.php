<?php

declare(strict_types = 1);

namespace Drupal\webprofiler;

/**
 * Generic class Decorator.
 */
class Decorator {

  /**
   * The original object to decorate.
   *
   * @var object
   */
  protected object $object;

  /**
   * Class constructor.
   *
   * @param object $object
   *   The original object to decorate.
   */
  public function __construct(object $object) {
    $this->object = $object;
  }

  /**
   * Return the original (i.e. non-decorated) object.
   *
   * @return object
   *   The original object.
   */
  public function getOriginalObject(): object {
    $object = $this->object;
    while ($object instanceof Decorator) {
      $object = $object->getOriginalObject();
    }

    return $object;
  }

  /**
   * Return the object if $method is a PHP callable, FALSE otherwise.
   *
   * @param string $method
   *   The method's name.
   * @param bool $checkSelf
   *   TRUE to check this decorator, FALSE to check the original object.
   *
   * @return bool|object
   *   The object if $method is a PHP callable, FALSE otherwise.
   */
  public function isCallable(string $method, bool $checkSelf = FALSE): bool|object {
    // Check the original object.
    $object = $this->getOriginalObject();
    if (is_callable([$object, $method])) {
      return $object;
    }

    // Check Decorators.
    $object = $checkSelf ? $this : $this->object;
    while ($object instanceof Decorator) {
      if (is_callable([$object, $method])) {
        return $object;
      }

      $object = $this->object;
    }

    return FALSE;
  }

  /**
   * Call a method on the original object, with specific arguments.
   *
   * @param string $method
   *   The method to call.
   * @param array $args
   *   The args to pass to the method.
   *
   * @return mixed
   *   The return of the method invocation on the original object.
   *
   * @throws \Exception
   */
  public function __call(string $method, array $args): mixed {
    if ($object = $this->isCallable($method)) {
      return call_user_func_array([$object, $method], $args);
    }

    throw new \Exception(
      'Undefined method - ' . get_class($this->getOriginalObject()) . '::' . $method,
    );
  }

  /**
   * Return the value of a property from the original object.
   *
   * @param string $property
   *   The property name.
   *
   * @return mixed|null
   *   The value of a property from the original object or NULL if the property
   *   doesn't exist on the original object.
   */
  public function __get(string $property): mixed {
    $object = $this->getOriginalObject();
    if (property_exists($object, $property)) {
      // @phpstan-ignore-next-line
      return $object->$property;
    }

    return NULL;
  }

}
