<?php

class Request {
  private $get = [];
  private $post = [];

  public function __construct()
  {
    $this->get = $_GET;
    $this->post = $_POST;
  }

  public function get(string $variable, ?string $default = null)
  {
    if (isset($this->get[$variable])) {
      return $this->get[$variable];
    }

    return $default;
  }

  public function post(string $variable, ?string $default = null)
  {
    if (isset($this->post[$variable])) {
      return $this->post[$variable];
    }

    return $default;
  }
}
