<?php
error_reporting(0);

/**
 * Eat and meet
 */
$json = file_get_contents("./storage/eatandmeet.json", "w");
$jsonData = json_decode($json, true);
$pokrmy = $jsonData['data'];

$timeDifference = (new DateTime())->getTimestamp() - $jsonData['timestamp'];

if ($timeDifference > 1500) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://eatandmeet.sk/tyzdenne-menu");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $dom = new DOMDocument();
    $dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;
    $parseNodes = ["day-1", "day-2", "day-3", "day-4", "day-5"];
    $pokrmy = [
        ["date" => date('d.m.Y', strtotime('monday this week')), "day" => "Pondelok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('tuesday this week')), "day" => "Utorok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('wednesday this week')), "day" => "Streda", "menu" => []],
        ["date" => date('d.m.Y', strtotime('thursday this week')), "day" => "Štvrtok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('friday this week')), "day" => "Piatok", "menu" => []]
    ];

    foreach ($parseNodes as $index => $nodeId) {
        $node = $dom->getElementById($nodeId);
        foreach ($node->childNodes as $menuItem) {
            if ($menuItem && $menuItem->childNodes->item(1) && $menuItem->childNodes->item(1)->childNodes->item(3)) {
                $nazov = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(1)->nodeValue);
                $cena = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(3)->nodeValue);
                $popis = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(3)->nodeValue);
                array_push($pokrmy[$index]["menu"], "$nazov ($popis): <b>$cena</b>");
            }
        }
    }

    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $pokrmy];

    $eatJson = fopen("./storage/eatandmeet.json", "w");
    fwrite($eatJson, json_encode($data));
    fclose($eatJson);
}


/**
 * Delikanti
 */

$json = file_get_contents("./storage/delikanti.json", "w");

$jsonData = json_decode($json, true);

$foods = $jsonData['data'];
$timeDifference = (new DateTime())->getTimestamp() - $jsonData['timestamp'];


if ($timeDifference > 1500) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.delikanti.sk/prevadzky/1-jedalen-fei-stu/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $dom = new DOMDocument();
    $dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;
    $tables = $dom->getElementsByTagName('table');
    $rows = $tables->item(0)->getElementsByTagName('tr');
    $index = 0;
    $dayCount = 0;
    $foods = [];
    $foodCount = $rows->item(0)->getElementsByTagName('th')->item(0)->getAttribute('rowspan');

    foreach ($rows as $row) {
        if ($row->getElementsByTagName('th')->item(0)) {
            $foodCount = $row->getElementsByTagName('th')->item(0)->getAttribute('rowspan');

            $day = trim($rows->item($index)->getElementsByTagName('th')->item(0)->getElementsByTagName('strong')->item(0)->nodeValue);

            $th = $rows->item($index)->getElementsByTagName('th')->item(0);

            foreach ($th->childNodes as $node)
                if (!($node instanceof \DomText))
                    $node->parentNode->removeChild($node);
            $date = trim($rows->item($index)->getElementsByTagName('th')->item(0)->nodeValue);
            if($dayCount==5){
                break;
            }
            array_push($foods, ["date" => $date, "day" => $day, "menu" => []]);

            for ($i = $index; $i < $index + intval($foodCount); $i++) {
                if ($foods[$dayCount])
                    array_push($foods[$dayCount]["menu"], trim($rows->item($i)->getElementsByTagName('td')->item(1)->nodeValue));
            }
            $index += intval($foodCount);
            $dayCount++;
        }
    }
    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $foods];

    $delikantiJson = fopen("./storage/delikanti.json", "w");
    fwrite($delikantiJson, json_encode($data));
    fclose($delikantiJson);
}

/**
 * Klubovna
 */

$json = file_get_contents("./storage/klubovna.json", "w");

$jsonData = json_decode($json, true);

$jedla = $jsonData['data'];
$timeDifference = (new DateTime())->getTimestamp() - $jsonData['timestamp'];

if ($timeDifference > 1500) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.nasaklubovna.sk/klubovne/karloveska/menu/denne-menu/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $dom = new DOMDocument();
    $dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;
    $parseNodes = ["pondelok_t", "utorok", "streda", "stvrtok", "piatok", "day-6", "day-7"];
    $jedla = [];

    $classname = 'row no-gutters dailymenu-list';
    $finder = new DomXPath($dom);
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
    $i = 0;
    foreach ($nodes as $index => $node) {
        if ($i % 3 == 0) {
            $polievka = $node->childNodes->item(3)->nodeValue;
            $date = $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->nodeValue;
            $jedla[$index]["date"] = trim("$date");
            $cena = $node->childNodes->item(5)->nodeValue;
            $jedla[$index]["menu"][] = trim("$polievka : $cena");
            $help = $index;
        } else {
            $jedlo = $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(3)->nodeValue;
            $cena = $node->childNodes->item(5)->nodeValue;
            $jedla[$help]["menu"][] = trim("$jedlo : <b>$cena</b>");
        }
        $i++;
    }

    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $jedla];

    $klubovnaJson = fopen("./storage/klubovna.json", "w");
    fwrite($klubovnaJson, json_encode($data));
    fclose($klubovnaJson);
}
?>
<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <title>Zadanie4</title>
</head>
<body>
<header>
    <h1>Jedálny lístok</h1>
</header>


<div class="container">
    <nav class="navbar navbar-expand-lg navbar-light ">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item week">
                    <a class="nav-link" href="#" onclick="allWeek()"><b>Celý týždeň</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="monday()"><b>Pondelok</b></a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="#" onclick="tuesday()"><b>Utorok</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="wednesday()"><b>Streda</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="thursday()"><b>Štvrtok</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="friday()"><b>Piatok</b></a>
                </li>

            </ul>
        </div>
    </nav>
    <table class="table">
        <thead>
        <tr>
            <th colspan="2"><h2>Eat and meet</h2></th>
        </tr>
        <tr>
            <th scope="col">Deň</th>
            <th scope="col" class="food">Jedlo</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($pokrmy as $item) {
            echo '<tr>';
            echo "<td>" .$item['day'] .  ' ' . $item['date'] .  "</td>";
            echo "<td>";
            foreach ($item["menu"] as $food) {
                echo $food;
                echo "<br>";
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

    <table class="table">
        <thead>
        <tr>
            <th colspan="2"><h2>Delikanti</h2></th>
        </tr>
        <tr>
            <th scope="col">Deň</th>
            <th scope="col" class="food">Jedlo</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($foods as $item) {
            echo '<tr>';
            echo "<td>" . $item['day'] .  ' ' . $item['date'] . "</td>";
            echo "<td>";
            foreach ($item["menu"] as $food) {
                echo $food;
                echo "<br>";
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

    <table class="table">
        <thead>
        <tr>
            <th colspan="2"><h2>Klubovňa</h2></th>
        </tr>
        <tr>
            <th scope="col">Deň</th>
            <th scope="col" class="food">Jedlo</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($jedla as $item) {
            echo '<tr>';
            echo "<td>" . $item['date'] . ' ' . $item['day'] . "</td>";
            echo "<td>";
            foreach ($item["menu"] as $food) {
                echo $food;
                echo "<br>";
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<script src="main.js"></script>
</body>
</html>
