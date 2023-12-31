<?php

session_start();

require_once('../config.php');
if (file_exists('../config.local.php')) {
  require_once('../config.local.php');
}

require_once('../src/autoloader.php');

$request = new Request();

if ($request->get('logout') === '1') {
  session_unset();
}

if ($request->post('login') && $request->post('password')) {
  if ($request->post('login') === $login && $request->post('password') === $password) {
    $_SESSION['login'] = $login;
  }
}

if (!isset($_SESSION['login'])) {
  echo '<form method="post" action="?">';
  echo '    <input type="text" name="login" placeholder="login">';
  echo '    <input type="password" name="password" placeholder="password">';
  echo '    <input type="submit">';
  echo '</form>';
  exit();
}

echo '<a href="?logout=1">Logout</a>';

if (!file_exists($database)) {
  $db = new PDO("sqlite:" . $database);

  $query = "CREATE TABLE locations (
    id VARCHAR(28) PRIMARY KEY,
    address VARCHAR(256)
  );";

  $db->query($query);


  $query = "CREATE TABLE visit (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    address VARCHAR(28),
    startTimestamp DATETIME,
    endTimestamp DATETIME
  );";

  $db->query($query);
} else {
  $db = new PDO("sqlite:" . $database);
}

$file = fopen($filename, 'r');
$data = fread($file, filesize($filename));
fclose($file);

$data = json_decode($data, true);


if (!array_key_exists('timelineObjects', $data)) {
  echo 'no timelineObjects';
  exit;
}

$locationCache = [];
$visitCache = [];

foreach ($data['timelineObjects'] as $key => $value) {
  if (array_key_exists('placeVisit', $value)) {
    if (array_key_exists('location', $value['placeVisit'])) {
      if (
        array_key_exists('address', $value['placeVisit']['location'])
        && array_key_exists('placeId', $value['placeVisit']['location'])
      ) {
        $id = $value['placeVisit']['location']['placeId'];
        if (!array_key_exists($id, $locationCache)) {
          $query = 'SELECT id FROM locations WHERE id = :id';
          $stmt = $db->prepare($query);
          $stmt->bindParam(":id", $id);
          $stmt->execute();
          $results = $stmt->fetchAll();
          if (count($results) === 0) {
            $query = "INSERT INTO locations (id, address) VALUES (?, ?);";
            $stmt = $db->prepare($query);
            $stmt->execute([$id,$value['placeVisit']['location']['address']]);
          }
          $locationCache[$id] = true;
        }

        $startTimestamp = $value['placeVisit']['duration']['startTimestamp'];
        $endTimestamp = $value['placeVisit']['duration']['endTimestamp'];
        $visitId = $startTimestamp . '-' . $endTimestamp;

        if (!array_key_exists($visitId, $visitCache)) {
          $query = 'SELECT id FROM visit WHERE startTimestamp = :startTimestamp AND endTimestamp = :endTimestamp';
          $stmt = $db->prepare($query);
          $stmt->bindParam(":startTimestamp", $startTimestamp);
          $stmt->bindParam(":endTimestamp", $endTimestamp);
          $stmt->execute();
          $results = $stmt->fetchAll();
          if (count($results) === 0) {
            $query = "INSERT INTO visit (address, startTimestamp, endTimestamp) VALUES (?, ?, ?);";
            $stmt = $db->prepare($query);
            $stmt->execute([$id, $startTimestamp, $endTimestamp]);
          }
          $visitCache[$id] = true;
        }
        
      }
    }
  }
}

$date_from = $request->get('date_from');
$date_to = $request->get('date_to');

$dateValudationRegex = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';

$sqlFrom = '';
if ($date_from !== null && $date_from !== '') {
  if(!preg_match($dateValudationRegex, $date_from)) {
      echo '<p>Wrong <b>from</b> date format. Should be: YYYY-MM-DD</p>';
      $date_from = '';
  } else {
    $sqlFrom = ' AND visit.startTimestamp >= "' . $date_from . '" ';
  }
}

$sqlTo = '';
if ($date_to !== null && $date_to !== '') {
  if(!preg_match($dateValudationRegex, $date_to)) {
      echo '<p>Wrong <b>to</b> date format. Should be: YYYY-MM-DD</p>';
      $date_to = '';
  } else {
    $sqlTo = ' AND visit.startTimestamp <= "' . $date_to . '" ';
  }
}

$query = 'SELECT locations.address, COUNT(*) AS c FROM visit JOIN locations ON visit.address = locations.id WHERE 1 ' . $sqlFrom . $sqlTo . ' GROUP BY locations.address ORDER BY c DESC;';
$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll();

echo '<h2>Visit top:</h2>';

foreach ($results as $result => $value) {
  echo $value['c'] . ' - ' . $value['address'] . '<br>' . "\n";
}

?>

<h2>Filter:</h2>
<form method="GET">
  <label>Date from</label>
  <input type="input" name="date_from" value="<?php echo $date_from; ?>">
  <label>Date to</label>
  <input type="input" name="date_to" value="<?php echo $date_to; ?>">
  <input type="submit" name="submit">
</form>
