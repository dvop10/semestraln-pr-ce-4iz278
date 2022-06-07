<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';


  if (empty($_SESSION['user_id'])){

      header('Location: index.php');
    exit('Pro úpravu příspěvků na nástěnce musíte být přihlášen(a).');

  }

  //pomocné proměnné pro přípravu dat do formuláře
  $playlistId='';
  $pname='';
  $sname=(!empty($_REQUEST['song'])?intval($_REQUEST['song']):'');;





#region načtení existujícího příspěvku z DB
  if (!empty($_REQUEST['id'])){
    $playlistQuery=$db->prepare('SELECT playlists.*, songs.song_id,songs.name AS song_name,songs.autor  FROM playlists JOIN songs using (song_id) WHERE playlist_id=:id LIMIT 1;');
    $playlistQuery->execute([':id'=>$_REQUEST['id']]);
    if ($playlist=$playlistQuery->fetch(PDO::FETCH_ASSOC)){
      //naplníme pomocné proměnné daty příspěvku
      $playlistId=$playlist['playlist_id'];
      $sname=$playlist['song_name'];
      $pname=$playlist['name'];
    }else{
      exit('Příspěvek neexistuje.');//tady by mohl být i lepší výpis chyby :)
    }
  }
  #endregion načtení existujícího příspěvku z DB

  $errors=[];
  if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola kategorie
    if (!empty($_POST['song'])){

      $songQuery=$db->prepare('SELECT * FROM songs WHERE song_id=:song LIMIT 1;');
      $songQuery->execute([
        ':song'=>$_POST['song']
      ]);
      if ($songQuery->rowCount()==0){
        $errors['song']='Zvolená kategorie neexistuje!';
        $playlistSong='';
      }else{
        $playlistSong=$_POST['song'];
      }

    }else{
      $errors['song']='Musíte vybrat kategorii.';
    }
    #endregion kontrola kategorie
    #region kontrola textu
    $playlistName=trim(@$_POST['name']);
    if (empty($playlistName)){
      $errors['name']='Musíte zadat text příspěvku.';
    }
    #endregion kontrola textu

    if (empty($errors)){
      #region uložení dat

      if ($playlistId){
        #region aktualizace existujícího příspěvku
        $saveQuery=$db->prepare('UPDATE playlists SET song_id=:song, name=:name, user_id=:user WHERE playlist_id=:id LIMIT 1;');
        $saveQuery->execute([
          ':song'=>$sname,
          ':name'=>$pname,
          ':id'=>$playlistId,
          ':user'=>$_SESSION['user_id']
        ]);
          echo $sname;
        #endregion aktualizace existujícího příspěvku
      }else{
        #region uložení nového příspěvku
        $saveQuery=$db->prepare('INSERT INTO playlists (user_id, song_id, name) VALUES (:user, :song, :name );');
        $saveQuery->execute([
          ':user'=>$_SESSION['user_id'],
          ':song'=>$sname,
          ':name'=>$pname
        ]);
        echo $sname;
        #endregion uložení nového příspěvku
      }

      #endregion uložení dat
      #region přesměrování
        header('Location: index.php?song='.$playlistSong);
      exit();
      #endregion přesměrování
    }
    #endregion zpracování formuláře
  }

  //vložíme do stránek hlavičku
  if ($playlistId){
    $pageTitle='Úprava příspěvku';
  }else{
    $pageTitle='Nový příspěvek';
  }

  include 'inc/header.php';
?>

  <form method="post">
    <input type="hidden" name="id" value="<?php echo $playlistId;?>" />

    <div class="form-group">
      <label for="song">Playlist:</label>
      <select name="pnames" id="pnames" required class="form-control <?php echo (!empty($errors['song'])?'is-invalid':''); ?>">
        <option value="">--vyberte--</option>
        <?php
          $pnameQuery=$db->prepare('SELECT * FROM playlists ORDER BY name;');
          $pnameQuery->execute();
          $pnames=$pnameQuery->fetchAll(PDO::FETCH_ASSOC);
          if (!empty($pnames)){
            foreach ($pnames as $pname){
              echo '<option value="'.$pname['name'].'" '.($pname['name']==$pname?'selected="selected"':'').'>'.htmlspecialchars($pname['name']).'</option>';

            }
          }
        ?>
      </select>
      <?php
        if (!empty($errors['pname'])){
          echo '<div class="invalid-feedback">'.$errors['pname'].'</div>';
        }
      ?>
    </div>

    <div class="form-group">
      <label for="name">Jmeno písničky:</label>
      <textarea name="sname" id="sname" required class="form-control <?php echo (!empty($errors['sname'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($sname)?></textarea>
      <?php
        if (!empty($errors['sname'])){
          echo '<div class="invalid-feedback">'.$errors['sname'].'</div>';
        }
      ?>
    </div>

    <button type="submit" class="btn btn-primary">uložit...</button>
    <a href="index.php" class="btn btn-light">zrušit</a>
  </form>

<?php
  //vložíme do stránek patičku
  include 'inc/footer.php';