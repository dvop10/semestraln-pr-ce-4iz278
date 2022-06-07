<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';

  //vložíme do stránek hlavičku
  include 'inc/header.php';
if (empty($_SESSION['user_id'])){

    header('Location: login.php');
    exit('Pro úpravu příspěvků na nástěnce musíte být přihlášen(a).');

}
$ratingquery = $db->prepare('SELECT * from rating where user_id=:user_id;');
$ratingquery->execute([
    ':user_id'=>$_SESSION['user_id']
]);
$ratings= $ratingquery->fetchAll(PDO::FETCH_ASSOC);





    if(!empty($_GET['like'])){
        $ratingquery = $db->prepare('SELECT * from rating where user_id=:user_id AND song_id=:song_id LIMIT 1;');
        $ratingquery->execute([
            ':user_id'=>$_SESSION['user_id'],
            ':song_id'=>$_GET['like']
        ]);

        if($ratingslike= $ratingquery->fetch(PDO::FETCH_ASSOC)){
            $saveQuery=$db->prepare('UPDATE rating SET rating="like" WHERE user_id=:user_id AND song_id=:song_id LIMIT 1');
            $saveQuery->execute([
                ':user_id'=>$_SESSION['user_id'],
                ':song_id'=>$_GET['like']
            ]);
            header('Location: index.php');
        }
        else{
            $saveQuery=$db->prepare('INSERT INTO rating (user_id,song_id,rating) VALUES (:user_id,:song_id,"like" );');
            $saveQuery->execute([
                ':user_id'=>$_SESSION['user_id'],
                ':song_id'=>$_GET['like']
            ]);
            header('Location: index.php');
        }




    }
    if(!empty($_GET['dislike'])){
        $ratingquery = $db->prepare('SELECT * from rating where user_id=:user_id AND song_id=:song_id LIMIT 1;');
        $ratingquery->execute([
            ':user_id'=>$_SESSION['user_id'],
            ':song_id'=>$_GET['dislike']
        ]);

        if($ratingdislike= $ratingquery->fetch(PDO::FETCH_ASSOC)){
            $saveQuery=$db->prepare('UPDATE rating SET rating="dislike" WHERE user_id=:user_id AND song_id=:song_id LIMIT 1');
            $saveQuery->execute([
                ':user_id'=>$_SESSION['user_id'],
                ':song_id'=>$_GET['dislike']
            ]);

            header('Location: index.php');
        }
        else{
        $saveQuery=$db->prepare('INSERT INTO rating (user_id,song_id,rating) VALUES (:user_id,:song_id,"dislike" );');
        $saveQuery->execute([
            ':user_id'=>$_SESSION['user_id'],
            ':song_id'=>$_GET['dislike']
        ]);

            header('Location: index.php');
        }

    }


  if (!empty($_GET['playlist'])){
    #region výběr příspěvků z konkrétního playlistu

    $query = $db->prepare('SELECT
                           playlists.*,songs.song_id AS song_id, users.name AS user_name, users.email, songs.name AS song_name, songs.autor AS song_autor
                           FROM playlists JOIN users USING (user_id) JOIN songs USING (song_id) WHERE playlists.name=:playlist AND user_id=:user_id;');
    $query->execute([
      ':playlist'=>$_GET['playlist'],
      ':user_id'=>$_SESSION['user_id']

    ]);
      $pisnicky = $query->fetchAll(PDO::FETCH_ASSOC);
    #endregion výběr příspěvků z konkrétní kategorie
  }else{

    #region výběr příspěvků bez ohledu na kategorii
    $query = $db->prepare('SELECT songs.name as song_name, songs.autor as song_autor, songs.song_id as song_id from songs;');
    $query->execute();
      $pisnicky = $query->fetchAll(PDO::FETCH_ASSOC);
    #region výběr příspěvků bez ohledu na kategorii
  }

  #region formulář s výběrem kategorií
  echo '<form method="GET" id="playlistFilterForm">
          <label for="playlist">Playlist:</label>
          <select name="playlist" id="playlist" onchange="document.getElementById(\'playlistFilterForm\');">
            <option value="">--nerozhoduje--</option>';

    $queryy=$db->prepare('SELECT DISTINCT name FROM playlists WHERE user_id=:user_id ORDER BY name;');
    $queryy->execute([
        ':user_id'=>$_SESSION['user_id']

    ]);
    $playlists = $queryy->fetchAll(PDO::FETCH_ASSOC);
  //$playlists=$db->query('SELECT * FROM playlists WHERE user_id=:user_id ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($playlists)){
    foreach ($playlists as $playlist){
      echo '<option value="'.$playlist['name'].'"';//u song_id nemusí být ošetření speciálních znaků, protože jde o číslo
      if ($playlist['name']==@$_GET['playlist']){
        echo ' selected="selected" ';
      }
      echo '>'.htmlspecialchars($playlist['name']).'</option>';
    }
  }

  echo '  </select>';

    echo '<button type="submit" class="btn btn-primary">Filtrovat</button>';
          //<input type="submit" value="OK" class="d-none" />
   echo     '</form>';
  #endregion formulář s výběrem kategorií
$r=0;

  if (!empty($pisnicky)){
    #region výpis příspěvků
    echo '<div class="row">';
    foreach ($pisnicky as $playlist){
      echo '<article class="col-12 col-md-6 col-lg-4 col-xxl-3 border border-dark mx-1 my-1 px-2 py-1">';
      echo '  <div><span class="badge badge-secondary">'.htmlspecialchars($playlist['song_autor']).'</span></div>';
      echo '  <div>'.nl2br(htmlspecialchars($playlist['song_name'])).'</div>';
      echo '  <div class="small text-muted mt-1">';
      echo '<a href="index.php?like='.$playlist['song_id'].'" class="btn btn-primary btn-sm mx-1 my-1 px-2 py-1">Like</a>';
      echo '<a href="index.php?dislike='.$playlist['song_id'].'" class="btn btn-primary btn-sm mx-1 my-1 px-2 py-1">Dislike</a>';
                //echo htmlspecialchars($playlist['user_name']);
      foreach($ratings as $rating ){
          if($rating['song_id']==$playlist['song_id']){
              echo 'aktuální hodnocení je:'.$rating['rating'];
              $r=1;
              break;
        }
          else {
              $r=2;
          }
      }
        if($r==2||empty($ratings))
        {
            echo "hodnocení není nastavené";
        }

                //echo date('d.m.Y H:i:s',strtotime($playlist['updated']));//datum získané z databáze převedeme na timestamp a ten pak do českého tvaru

               // if (!empty($_SESSION['user_id'])){
                 // echo ' - <a href="edit.php?id='.$playlist['playlist_id'].'" class="text-danger">upravit</a>';
                //()}

      echo '  </div>';
      echo '</article>';
    }
    echo '</div>';
    #endregion výpis příspěvků
  }else{
    echo '<div class="alert alert-info">Nebyly nalezeny žádné příspěvky.</div>';
  }
#přidat song
  if (!empty($_SESSION['user_id']&& !empty(@$_GET['playlist']))){
    echo '<div class="row my-3">
            <a href="addsongtoplaylist.php?playlist='.@$_GET['playlist'].'" class="btn btn-primary">Přidat písničku do vybraného playlistu</a>
          </div>';
  }

  #přidat playlist

    echo '<div class="row my-3">
            <a href="playlist.php" class="btn btn-primary">Přidat playlist</a>
          </div>';


    echo '<div class="row my-3">
            <a href="addsong.php" class="btn btn-primary">Přidat písničku do písniček</a>
          </div>';

    echo '<div class="row my-3">
            <a href="bestofbest.php" class="btn btn-primary">best of best</a>
          </div>';



  //vložíme do stránek patičku
  include 'inc/footer.php';