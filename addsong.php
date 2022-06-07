<?php


//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';


if (empty($_SESSION['user_id'])){

    header('Location: login.php');
    exit('Pro úpravu příspěvků na nástěnce musíte být přihlášen(a).');

}

//pomocné proměnné pro přípravu dat do formuláře




$songname= '';
$autorname= '';
$errors=[];


if (!empty($_POST['autorname'] )&& !empty($_POST['songname'])){
    $autorname = $_POST['autorname'];
    $songname = $_POST['songname'];


    $playlistQuery=$db->prepare("SELECT * FROM songs WHERE name=:songname AND autor=:autorname LIMIT 1;");
    $playlistQuery->execute([
        ':autorname'=>$autorname,
        ':songname'=>$songname
    ]);
    if ($playl=$playlistQuery->fetch(PDO::FETCH_ASSOC)){ # duplicate actor entry
        //echo "Playlist už tam je";
        if ($_POST['songname'] == $playl['name']&&$_POST['autorname'] == $playl['autor']){
            $errors['songname']='Písnička už je v písničkach';
            //echo $errors['songname'];
        }

    } else { # unique actor entry


        $saveQuery=$db->prepare('INSERT INTO songs (name,autor) VALUES (:songname,:autorname );');
        $saveQuery->execute([
            ':songname'=>$_POST['songname'],
            ':autorname'=>$_POST['autorname'],

        ]);





        header('Location: index.php');

        exit();
    }
}
$pageTitle='Pridani songu';

include 'inc/header.php';



?>
    <form method="post">
        <input type="hidden" name="id" " />



        <div class="form-group">
            <label for="autorname">jmeno autora:</label>
            <input type="text" name="autorname" id="autorname" required class="form-control <?php echo (!empty($errors['autorname'])?'is-invalid':''); ?>value='<?php echo htmlspecialchars($autorname)?>'">
            <?php
            if (!empty($errors['autorname'])){
                echo '<div class="invalid-feedback">'.$errors['autorname'].'</div>';
            }
            ?>
        </div>
        <div class="form-group">
            <label for="songname">jmeno songu:</label>
            <input type=text name="songname" id="songname" required class="form-control<?php echo (!empty($errors['songname'])?'is-invalid':''); ?>value='<?php echo htmlspecialchars($songname)?>'">
            <?php
            if (!empty($errors['songname'])){
                echo '<div class="text-danger">'.$errors['songname'].'</div>';
            }
            ?>
        </div>
        <button type="submit" class="btn btn-primary">uložit</button>
        <a href="index.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';