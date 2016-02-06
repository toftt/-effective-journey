<?php
    require_once 'private/check_login.php';
    require_once 'private/sqldetails.php';
    require_once 'private/mysql_fix_string.php';
    $connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
    $no_image = true;

    if(isset($_GET['img_id']))
    {
        $img_id = mysql_entities_fix_string($connection, $_GET['img_id']);
        $query = "SELECT * FROM user_images WHERE img_id='$img_id' AND user_id='$user_id'";

        $result = $connection->query($query);
        if (!$result) die($connection->error);

        if ($result->num_rows)
        {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $img_path = $row['path'];
            $img_title = $row['title'];

            $no_image = false;
        }
    }

    if ($no_image)
    {
        $query = "SELECT * FROM user_images WHERE user_id='$user_id' ORDER BY img_id LIMIT 1";
        $result = $connection->query($query);
        if (!$result) die($connection->error);

        if ($result->num_rows)
        {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            header("Location: images.php?img_id=" . $row['img_id'] . "#img_viewer");
            exit();
        }
    }

?>

<html>
<head>
    <title>Site</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script>

    window.onpopstate = function(event)
    {
        if (event.state || event.state == 0)
        {
            current_img = event.state;
            $("#img_display").attr("src", paths[current_img]);
            updateTitle();
        }
    }

    function updateImage(dir)
    {
        current_img += dir;
        if (current_img < 0) current_img = paths.length-1;
        if (current_img > paths.length-1) current_img = 0;
        $("#img_display").attr("src", paths[current_img]);
        window.history.pushState(current_img, "Title", "images.php?img_id=" + img_ids[current_img]);
    }

    function updateTitle()
    {
        $("#img_title > h2").text(titles[current_img]);
    }

    var img_ids = [], titles = [], paths = [];
    var current_img;

    <?php

    $query = "SELECT * FROM user_images WHERE user_id='$user_id' ORDER BY img_id";
    $result = $connection->query($query);
    if (!$result) die($connection->error);

    $rows = $result->num_rows;

    for ($j = 0; $j < $rows; ++$j)
    {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_ASSOC);

        echo "img_ids.push(\"" . $row['img_id'] ."\");";
        echo "titles.push(\"" . $row['title'] ."\");";
        echo "paths.push(\"" . $row['path'] ."\");";

    }

    echo "for(var i = 0; i < img_ids.length; ++i) {";
    echo "if (img_ids[i] == $img_id) current_img = i;";
    echo "}";

    ?>

    </script>
    <link rel='stylesheet' type='text/css' href='css/main.css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
</head>

<body>
<?php include_once 'private/top_and_menu.php'; ?>
<div id='img_viewer'>

<?php
    if (!$no_image)
    {
        echo "<div id='img_title'>";
        echo "<h2>$img_title</h2>";
        echo "</div>";
        echo "<div id='img_area'>";
        echo "<img id='img_display' src='$img_path' onload='updateTitle()'>";
        echo "</div>";
        echo "<div class='img_buttons'>";
        echo "<button class='stnd-button-large' onclick='updateImage(-1)'>Prev image</button>";
        echo "<button class='stnd-button-large' onclick='updateImage(1)'>Next image</button>";
        echo "</div>";
        echo "<div class='img_buttons'>";
        echo "<a class='stnd-button-large'href='upload.php'>Upload image</a>";
        echo "</div>";
    }
    else echo "<p>No images uploaded yet.</p> <a class='stnd-button'href='upload.php'>Upload one now</a>";

?>

</div>

<div id='bottom'>
</div>
<script src="js/dropdown-menu.js"></script>
<?php
    if (!$no_image)
    {
        echo "<script> window.history.replaceState(current_img, 'Title', 'images.php?img_id=' + img_ids[current_img] + '#img_viewer');</script>";
    }
?>
</body>
</html>