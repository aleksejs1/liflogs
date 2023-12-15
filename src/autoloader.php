<?php

function loadClass(string $class): void
{
  $map = ['Request' => '../src/Request.php'];

  if (array_key_exists($class, $map)) {
    include $map[$class];
  }
}

spl_autoload_register('loadClass');
