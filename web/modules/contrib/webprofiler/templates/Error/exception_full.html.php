<!-- <?= $_message = sprintf('%s (%d %s)', $exceptionMessage, $statusCode, $statusText); ?> -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="robots" content="noindex,nofollow"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?php echo $_message; ?></title>
  <link rel="icon" type="image/png"
        href="<?php echo $this->include('assets/images/favicon.png.base64'); ?>">
  <style><?php echo $this->include('assets/css/exception.css'); ?></style>
  <style><?php echo $this->include('assets/css/exception_full.css'); ?></style>
</head>
<body>
<script>
  document.body.classList.add(
    localStorage.getItem('symfony/profiler/theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'theme-dark' : 'theme-light')
  );
</script>

<?php if (class_exists(\Symfony\Component\HttpKernel\Kernel::class)) { ?>
  <header>
    <div class="container">
      <h1 class="logo"><?= $this->include('assets/images/drupal-10.svg'); ?> Drupal Exception</h1>

      <div class="help-link">
        <a href="https://www.drupal.org/documentation">
          <span class="icon"><?= $this->include('assets/images/icon-book.svg'); ?></span>
          <span class="hidden-xs-down">Drupal</span> Docs
        </a>
      </div>
    </div>
  </header>
<?php } ?>

<?= $this->include('exception.html.php', $context); ?>

<script>
  <?= $this->include('assets/js/exception.js'); ?>
</script>
</body>
</html>
<!-- <?= $_message; ?> -->
