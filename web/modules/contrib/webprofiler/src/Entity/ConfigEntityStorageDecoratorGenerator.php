<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\webprofiler\DecoratorGeneratorInterface;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * Generate decorators for config entity storage classes.
 */
class ConfigEntityStorageDecoratorGenerator implements DecoratorGeneratorInterface {

  /**
   * DecoratorGenerator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity type manager service.
   */
  public function __construct(protected readonly EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * {@inheritdoc}
   */
  public function generate(): void {
    $classes = $this->getClasses();

    foreach ($classes as $class) {
      try {
        $methods = $this->getMethods($class);
        $body = $this->createDecorator($class, $methods);
        $this->writeDecorator($class['id'], $body);
      }
      catch (\Exception $e) {
        throw new \Exception('Unable to generate decorator for class ' . $class['class'] . '. ' . $e->getMessage());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDecorators(): array {
    return [
      'taxonomy_vocabulary' => '\Drupal\webprofiler\Entity\VocabularyStorageDecorator',
      'user_role' => '\Drupal\webprofiler\Entity\RoleStorageDecorator',
      'shortcut_set' => '\Drupal\webprofiler\Entity\ShortcutSetStorageDecorator',
      'image_style' => '\Drupal\webprofiler\Entity\ImageStyleStorageDecorator',
    ];
  }

  /**
   * Return information about every config entity storage classes.
   *
   * @return array
   *   Information about every config entity storage classes.
   */
  private function getClasses(): array {
    $definitions = $this->entityTypeManager->getDefinitions();
    $classes = [];

    foreach ($definitions as $definition) {
      try {
        $classPath = $this->getClassPath($definition->getStorageClass());
        $ast = $this->getAst($classPath);

        $visitor = new FindingVisitor(function (Node $node) {
          return $this->isConfigEntityStorage($node);
        });

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->addVisitor(new NameResolver());
        $traverser->traverse($ast);

        $nodes = $visitor->getFoundNodes();

        /** @var \PhpParser\Node\Stmt\Class_ $node */
        foreach ($nodes as $node) {
          $classes[$definition->id()] = [
            'id' => $definition->id(),
            'class' => $node->name->name,
            'interface' => '\\' . implode('\\', $node->implements[0]->getParts()),
            'decoratorClass' => '\\Drupal\\webprofiler\\Entity\\' . $node->name->name . 'Decorator',
          ];
        }
      }
      catch (Error $error) {
        echo "Parse error: {$error->getMessage()}\n";
        return [];
      }
      catch (\ReflectionException $error) {
        echo "Reflection error: {$error->getMessage()}\n";
        return [];
      }
    }

    return $classes;
  }

  /**
   * Get the filename of the file in which the class has been defined.
   *
   * @param string $class
   *   A class name.
   *
   * @return string
   *   The filename of the file in which the class has been defined.
   *
   * @throws \ReflectionException
   */
  private function getClassPath(string $class): string {
    $reflector = new \ReflectionClass($class);

    return $reflector->getFileName();
  }

  /**
   * Parses PHP code into a node tree.
   *
   * @param string $classPath
   *   The filename of the file in which a class has been defined.
   *
   * @return \PhpParser\Node\Stmt[]|null
   *   Array of statements.
   */
  private function getAst(string $classPath): ?array {
    $code = file_get_contents($classPath);
    $parser = (new ParserFactory())->createForHostVersion();

    return $parser->parse($code);
  }

  /**
   * Return TRUE if this Node represents a config entity storage class.
   *
   * @param \PhpParser\Node $node
   *   The Node to check.
   *
   * @return bool
   *   TRUE if this Node represents a config entity storage class.
   */
  private function isConfigEntityStorage(Node $node): bool {
    if (!$node instanceof Class_) {
      return FALSE;
    }

    if ($node->extends !== NULL &&
      $node->extends->getParts()[0] == 'ConfigEntityStorage' &&
      isset($node->implements[0]) &&
      $node->implements[0]->getParts()[0] != ''
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Create the decorator from class information.
   *
   * @param array $class
   *   The class information.
   *
   * @return array
   *   The methods of the class.
   *
   * @throws \Exception
   */
  private function getMethods(array $class): array {
    $classPath = $this->getClassPath($class['interface']);
    $ast = $this->getAst($classPath);

    $nodeFinder = new NodeFinder();
    $nodes = $nodeFinder->find($ast, function (Node $node) {
      return $node instanceof ClassMethod;
    });

    $methods = [];
    /** @var \PhpParser\Node\Stmt\ClassMethod $node */
    foreach ($nodes as $node) {
      $params = [];
      /** @var \PhpParser\Node\Param $param */
      foreach ($node->getParams() as $param) {
        $params[] = $param->var->name;
      }

      $methods[] = [
        'name' => $node->name->name,
        'params' => $params,
      ];
    }

    return $methods;
  }

  /**
   * Create the decorator from class information and methods.
   *
   * @param array $class
   *   The class information.
   * @param array $methods
   *   The methods of the class.
   *
   * @return string
   *   The decorator class body.
   */
  private function createDecorator(array $class, array $methods): string {
    $decorator = $class['class'] . 'Decorator';

    $file = new PhpFile();
    $file->addComment('This file is auto-generated.');
    $namespace = $file->addNamespace(new PhpNamespace('Drupal\webprofiler\Entity'));

    $generated_class = $namespace->addClass($decorator);
    $generated_class->setExtends(ConfigEntityStorageDecorator::class);
    $generated_class->addImplement($class['interface']);
    foreach ($methods as $method) {
      $generated_method = $generated_class
        ->addMethod($method['name']);

      foreach ($method['params'] as $param) {
        $generated_method->addParameter($param);
      }

      $generated_method
        ->addBody(
          'return $this->getOriginalObject()->?(...?);',
          [
            $method['name'],
            array_map(function ($param) {
              return new Literal('$' . $param);
            }, $method['params']),
          ],
        );
    }

    $printer = new PsrPrinter();

    return $printer->printFile($file);
  }

  /**
   * Write a decorator class body to file.
   *
   * @param string $name
   *   The class name.
   * @param string $body
   *   The class body.
   */
  private function writeDecorator(string $name, string $body): void {
    $storage = PhpStorageFactory::get('webprofiler');

    if (!$storage->exists($name)) {
      $storage->save($name, $body);
    }
  }

}
