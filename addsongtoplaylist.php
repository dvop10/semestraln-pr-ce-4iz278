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


if (!empty($_POST['autornames'] )){
    $autorname = $_POST['autornames'];

    //echo $autorname;
    //exit();

    $arr_song_autor=explode("-",$autorname);

    $songQuery=$db->prepare("SELECT * FROM songs WHERE name=:songname AND autor=:autorname LIMIT 1;");
    $songQuery->execute([
        ':autorname'=>$arr_song_autor[0],
        ':songname'=>$arr_song_autor[1]
    ]);
    $song=$songQuery->fetch(PDO::FETCH_ASSOC);


    $playlQuery=$db->prepare("SELECT * FROM playlists WHERE name=:playlname and user_id=:user_id;");

    $playlQuery->execute([
        ':user_id'=>$_SESSION['user_id'],
        ':playlname'=>$_GET['playlist']]);
    $playl=$playlQuery->fetchAll(PDO::FETCH_ASSOC);


    //var_dump($playl);
    //var_dump($song);
    $p=0;
        foreach($playl as $playll){
            if ($playll['song_id']==$song['song_id']){
                $errors['songname']='song už je v playlist';
                $p=1;
            }

        }



if($p==0){
       $saveQuery=$db->prepare('INSERT into playlists (user_id,name,song_id) VALUES(:user_id,:name,:song_id  );');
        $saveQuery->execute([
            //':playlist_id'=>$playl['playlist_id'],
            ':user_id'=>$_SESSION['user_id'],
            ':name'=>$_GET['playlist'],
            ':song_id'=>$song['song_id'],
        ]);





        header('Location: index.php');

        exit();
    }
}
$pageTitle='Pridani songu';

include 'inc/header.php';

//isset($_POST['name']) ? $movieActorsE = $_POST['movieActors'] : $movieActorsE = '';

?>
    <form method="post">
        <input type="hidden" name="id"  />

        <div class="form-group">
            <label for="autornames">Autorname:</label>
            <select name="autornames" id="autornames" required class="form-control <?php echo (!empty($errors['autorname'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $autornameQuery=$db->prepare('SELECT autor,name FROM songs ORDER BY name;');
                $autornameQuery->execute();
                $autornames=$autornameQuery->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($autornames)){
                    foreach ($autornames as $autorname){
                        echo '<option value="'.$autorname['autor'].'-'.$autorname['name'].'" >'.htmlspecialchars($autorname['autor']."-".$autorname['name']).'</option>';

                    }
                }
                ?>
            </select>
            <?php
            if (!empty($errors['autorname'])){
                echo '<div class="invalid-feedback">'.$errors['autorname'].'</div>';
            }
            ?>


        </div>
        <button type="submit" class="btn btn-primary">uložit</button>
        <a href="index.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';