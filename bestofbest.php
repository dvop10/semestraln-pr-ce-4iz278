<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';

//vložíme do stránek hlavičku
include 'inc/header.php';
if (empty($_SESSION['user_id'])){

    header('Location: login.php');
    exit('Pro úpravu příspěvků na nástěnce musíte být přihlášen(a).');

}
$arl=[];
$ard=[];
$ratingquery = $db->prepare('SELECT * from rating;');
$ratingquery->execute(//[
    //':user_id'=>$_SESSION['user_id']
//]
);
$ratings= $ratingquery->fetchAll(PDO::FETCH_ASSOC);

    foreach($ratings as $rating){

        $ratequery = $db->prepare('SELECT song_id, COUNT(rating) FROM rating WHERE song_id=:song_id AND rating="LIKE";');
        $ratequery->execute([
            ':song_id'=>$rating['song_id']
        ]);

        $rates=$ratequery->fetch(PDO::FETCH_ASSOC);
        $r=$rates['song_id'];
        $c=$rates['COUNT(rating)'];
        if(intval($c)>0){
            $arl[$r]=$c;
        }



        //array_push($rates,$rates);
    }
    foreach($ratings as $rating){

        $ratequery = $db->prepare('SELECT song_id, COUNT(rating) FROM rating WHERE song_id=:song_id AND rating="DISLIKE";');
        $ratequery->execute([
            ':song_id'=>$rating['song_id']
        ]);

        $rates=$ratequery->fetch(PDO::FETCH_ASSOC);
        $r=$rates['song_id'];
        $c=$rates['COUNT(rating)'];
        if(intval($c)>0){
            $ard[$r]=$c;
        }


        //array_push($rates,$rates);
    }

    $ratesfinish=[];
    $ratesfinish2=[];

foreach($arl as $keyl =>$arll){

    foreach($ard as $keyd => $ardd){
        //echo $keyl;
        //echo $keyd;
        if($keyl==$keyd){
            $ratesfinish[$keyl]=$arll-$ardd;
            $ratesfinish2[$keyl]=$arll-$ardd;
        }
    }
}

foreach($ard as $keyd =>$ardd){
    foreach($ratesfinish as $keyf =>$ratesfinishh){
        //echo "keyd ".$keyd;
        //echo "   ";
        //echo "keyf".$keyf;
        if($keyf!=$keyd){
            $ratesfinish[$keyd]=(-$ardd);
            //echo "a";
        }

    }

}

//var_dump($ratesfinish);
foreach($arl as $keyl =>$arll){
    foreach($ratesfinish as $keyf =>$ratesfinishh){
        if($keyf!=$keyl){
            $ratesfinish[$keyl]=$arll;

            }
        foreach($ratesfinish2 as $keyf2 => $ratesfinishh2){
            if($keyf2==$keyl){
                $ratesfinish[$keyl]=$ratesfinish2[$keyl];
                }
            }
        }
}

    arsort($ratesfinish);
    #region výběr příspěvků bez ohledu na kategorii
    $query = $db->prepare('SELECT * FROM songs ;');
    $query->execute();
    $pisnicky = $query->fetchAll(PDO::FETCH_ASSOC);
    #region výběr příspěvků bez ohledu na kategorii

if (!empty($pisnicky)){
    #region výpis příspěvků
    echo '<div class="row">';
    foreach($ratesfinish as $key => $ratesfinishh){
        //echo $key;
        foreach ($pisnicky as $playlist){

            if($key == $playlist['song_id']){

            echo '<article class="col-12 col-md-6 col-lg-4 col-xxl-3 border border-dark mx-1 my-1 px-2 py-1">';
            echo '  <div><span class="badge badge-secondary">'.htmlspecialchars($playlist['autor']).'</span></div>';
            echo '  <div>'.nl2br(htmlspecialchars($playlist['name'])).'</div>';
            echo '  <div class="small text-muted mt-1">';
            echo '  </div>';
                echo "rating:".$ratesfinishh;
            echo '</article>';

                }
            }
    }
    echo '</div>';
    #endregion výpis příspěvků
}else{
    echo '<div class="alert alert-info">Nebyly nalezeny žádné příspěvky.</div>';
}


echo '<div class="row my-3">
            <a href="index.php" class="btn btn-primary">vrať se na domovskou stránku</a>
          </div>';



//vložíme do stránek patičku
include 'inc/footer.php';