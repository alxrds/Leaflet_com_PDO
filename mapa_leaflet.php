<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

define('HOST', 'localhost');
define('USER', 'alexandre.rodrigues');
define('PASS', 'ygk86tf3yk2qbaddf39f');
define('BASE', 'pci');

try {
    $conn = new pdo('mysql:host=' . HOST . ';dbname=' . BASE, USER, PASS);
} catch (PDOException $erro) {
    echo 'Erro: Falha ao Conectar' . $erro->getMessage();
}

if (!empty($_POST['bairro'])) {

    $search = $_POST['bairro'];
    $output = '';
    $arr = array();

    $stmt = $conn->prepare("SELECT * FROM pci.enderecos_viaveis 
    WHERE bairro LIKE :keywords
    GROUP BY cod_logradouro");
    $stmt->bindValue(':keywords', '%' . $search . '%');
    $stmt->execute();
    if ($stmt->rowCount() > 0) {

        $row = $stmt->fetch();

        $latitude0  = str_replace(',', '.', $row['latitude']);
        $longitude0 = str_replace(',', '.', $row['longitude']);

        while ($row = $stmt->fetch()) {
            $arr[] = ["latitude" => str_replace(',', '.', $row['latitude']), "longitude" => str_replace(',', '.', $row['longitude'])];
        }
    } else {

        $output .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Bairro não encontrado!</strong> Tente remover acentos ou caracteres especiais.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';

        echo $output;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <title> Endereços Viáveis | Telemont </title>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="icone_telemont.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="shortcut icon" type="image/x-icon" href="docs/images/favicon.ico" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <style>
        .search-container {
            width: 70%;
            display: block;
            margin: 0 auto;
        }

        input#search-bar {
            margin: 0 auto;
            width: 100%;
            height: 45px;
            padding: 0 20px;
            font-size: 1rem;
            border: 1px solid #D0CFCE;
            outline: none;
        }

        .search-icon {
            position: relative;
            float: right;
            width: 75px;
            height: 75px;
            top: -62px;

        }

        #map {
            width: 100%;
            height: 400px;
        }
    </style>

</head>

<body>

    <form method="post" id="myForm" class="search-container">

        <input type="text" id="search-bar" name="bairro" placeholder="Bairro" required>
        <a href="#" onclick="formSubmit()"><img class="search-icon" src="http://www.endlessicons.com/wp-content/uploads/2012/12/search-icon.png"></a>

    </form>

    <?php

    if ($_POST['bairro'] && $stmt->rowCount() > 0) {

        echo '<div id="map"></div>';
    }

    ?>

    <script>
        function formSubmit() {
            document.getElementById("myForm").submit();
        }

        var map = L.map('map').setView(['<?= $latitude0 ?>', '<?= $longitude0 ?>'], 15);
        var arr = JSON.parse('<?php echo json_encode($arr) ?>');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var LeafIcon = L.Icon.extend({
            options: {
                iconSize: [20, 20],
                shadowSize: [50, 64],
                iconAnchor: [22, 94],
                shadowAnchor: [4, 62],
                popupAnchor: [-3, -76]
            }
        });

        var telemont = new LeafIcon({
            iconUrl: 'icone_telemont.png'
        });

        function mostrarLocais() {
            for (var i = 0; i < arr.length; i++) {
                L.marker([arr[i]['latitude'], arr[i]['longitude']], {
                    icon: telemont
                }).bindPopup("Aqui não tem fibra!").addTo(map);
            }
        }

        mostrarLocais();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>

</body>

</html>