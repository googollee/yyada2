<?php

function proxy() {
  $cookie = "";
  foreach ($_COOKIE as $key => $value) {
    $cookie = $cookie.$key."=".$value."; ";
  }
  $post = "";
  foreach ($_POST as $key => $value) {
    $post = $post.$key."=".urlencode($value)."&";
  }

  $url = "https://mobile.twitter.com/session";
  $ch = curl_init( $url );
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_COOKIE, $cookie);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $schema = "http://";
  if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")) {
    $schema = "https://";
  }
  $response = curl_exec($ch);
  $response = str_replace("https://mobile.twitter.com", $schema.$_SERVER["SERVER_NAME"], $response);
  $response = explode("\r\n\r\n", $response, 2);

  foreach (explode("\r\n", $response[0], -1) as $line) {
    header($line, false);
  }
  echo $response[1];
}

function deny() {
  header("HTTP/1.1 401 Unauthorized");
  echo "You are not prepared!";
}

function is_invite($user) {
  $invite_file = __DIR__ . '/' . '/invite.txt';
  if (!is_file($invite_file)) return true;

  $allowed_users = file('invite.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!in_array(strtolower($user), $allowed_users)) {
    return false;
  }
  return true;
}

$user = $_POST["username"];
if (is_invite($user)) {
  proxy();
} else {
  deny();
}
