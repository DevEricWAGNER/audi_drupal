<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\MethodData;

/**
 * Trait with common code for data collectors.
 */
trait DataCollectorTrait {

  /**
   * Return information about a method of a class.
   *
   * @param mixed $class
   *   A class name.
   * @param string $method
   *   A method's name.
   *
   * @return \Drupal\webprofiler\MethodData|null
   *   Array of information about a method of a class.
   */
  public function getMethodData($class, string $method): ?MethodData {
    $class = is_object($class) ? get_class($class) : $class;
    $data = NULL;

    try {
      $reflectedMethod = new \ReflectionMethod($class, $method);

      $data = new MethodData(
        $class,
        $method,
        $reflectedMethod->getFileName(),
        $reflectedMethod->getStartLine() ? $reflectedMethod->getStartLine() : '',
      );
    }
    catch (\ReflectionException $re) {
    }
    finally {
      return $data;
    }
  }

  /**
   * Retrieve the context of callable for debugging purposes.
   *
   * @param callable $callable
   *   The callable to retrieve the context for.
   *
   * @return string
   *   The context of the callable.
   */
  private function getCallableContext(callable $callable): string {
    switch (TRUE) {
      case \is_string($callable) && \strpos($callable, '::'):
        $parts = explode('::', $callable);
        return sprintf('class: %s, static method: %s', $parts[0], $parts[1]);

      case \is_string($callable):
        return sprintf('function: %s', $callable);

      case \is_array($callable) && \is_object($callable[0]):
        return sprintf('class: %s, method: %s', \get_class($callable[0]), $callable[1]);

      case \is_array($callable):
        return sprintf('class: %s, static method: %s', $callable[0], $callable[1]);

      case $callable instanceof \Closure:
        try {
          $reflectedFunction = new \ReflectionFunction($callable);
          $closureClass = $reflectedFunction->getClosureScopeClass();
          $closureThis = $reflectedFunction->getClosureThis();
        }
        catch (\ReflectionException $e) {
          return 'closure';
        }

        return sprintf(
            'closure this: %s, closure scope: %s, static variables: %s',
            $closureThis ? \get_class($closureThis) : $reflectedFunction->name,
            $closureClass != NULL ? $closureClass->getName() : $reflectedFunction->name,
            $this->formatVariablesArray($reflectedFunction->getStaticVariables()),
          );

      case \is_object($callable):
        return sprintf('invokable: %s', \get_class($callable));

      default:
        return 'unknown';
    }
  }

  /**
   * Format variables array in order to avoid huge objects dumping.
   *
   * @param array $data
   *   The array to format.
   *
   * @return string
   *   The formatted array.
   */
  private function formatVariablesArray(array $data): string {
    foreach ($data as $key => $value) {
      if (\is_object($value)) {
        $data[$key] = \get_class($value);
      }
      elseif (\is_array($value)) {
        $data[$key] = $this->formatVariablesArray($value);
      }
    }

    return implode(', ', $data);
  }

  /**
   * Convert a numeric value to a human-readable string.
   *
   * @param string $value
   *   The value to convert.
   *
   * @return int
   *   A human-readable string.
   */
  private function convertToBytes(string $value): int {
    if ('-1' == $value) {
      return -1;
    }

    $value = strtolower($value);
    $max = strtolower(ltrim($value, '+'));
    if (str_starts_with($max, '0x')) {
      $max = intval($max, 16);
    }
    elseif (str_starts_with($max, '0')) {
      $max = intval($max, 8);
    }
    else {
      $max = intval($max);
    }

    $max *= match (substr($value, -1)) {
      't' => 1024 * 1024 * 1024 * 1024,
      'g' => 1024 * 1024 * 1024,
      'm' => 1024 * 1024,
      'k' => 1024,
      default => 0,
    };

    return $max;
  }

}
