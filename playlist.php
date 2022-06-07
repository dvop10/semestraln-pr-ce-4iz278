<?php


//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';


if (empty($_SESSION['user_id'])){

    header('Location: login.php');
    exit('Pro úpravu příspěvků na nástěnce musíte být přihlášen(a).');

}

//pomocné proměnné pro přípravu dat do formuláře



    $name= '';
    $errors=[];


if (!empty($_POST['pname'])){
$playlist = $_POST['pname'];



    $playlistQuery=$db->prepare("SELECT name FROM playlists WHERE name=:pname LIMIT 1;");
    $playlistQuery->execute([
        ':pname'=>$playlist
    ]);
    if ($playl=$playlistQuery->fetch(PDO::FETCH_ASSOC)){ # duplicate actor entry
        //echo "Playlist už tam je";
        if ($_POST['pname'] == $playl['name']){
            $errors['pname']='Playlist už tam je';
        }

    } else { # unique actor entry


        $saveQuery=$db->prepare('INSERT INTO playlists (name,user_id) VALUES (:pname,:user );');
        $saveQuery->execute([
            ':pname'=>$playlist,
            ':user'=>$_SESSION['user_id']
        ]);





    header('Location: index.php');

    exit();
    }
}
    $pageTitle='Pridani playlistu';

    include 'inc/header.php';

    //isset($_POST['name']) ? $movieActorsE = $_POST['movieActors'] : $movieActorsE = '';

?>
<form method="post">
    <input type="hidden" name="id" " />



    <div class="form-group">
        <label for="pname">jmeno playlistu:</label>
        <input type="text" name="pname" id="pname" required class="form-control <?php echo (!empty($errors['pname'])?'is-invalid':''); ?>value='<?php echo htmlspecialchars($name)?>'">
        <?php
        if (!empty($errors['pname'])){
            echo '<div class="invalid-feedback">'.$errors['pname'].'</div>';
        }
        ?>
    </div>

    <button type="submit" class="btn btn-primary">uložit</button>
    <a href="index.php" class="btn btn-light">zrušit</a>
</form>

    <?php
    //vložíme do stránek patičku
    include 'inc/footer.php';