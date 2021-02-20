<?php
/* Lösungsvariante: alle Datensätze aus Datenbank Movie Tabellen werden im verlangten Format importiert */

//Hier wird einfach alles gesucht, was es aktuell gibt und wird in die neue Datenbank eingefügt
$query = 'drop database if exists dbmovie';
$query1 = 'create database dbmovie';
$query2 = 'use dbmovie';
$query3 = 'create table tblpremiere (pre_id int primary key auto_increment, genre varchar(255), filmtitel varchar(255), jahr int, regie varchar(255))';
$insertMovie = 'insert into tblpremiere (genre, filmtitel, jahr, regie) 
                select gen_name as "Genre", 
              concat_ws(\' \', mov_title_1, mov_title_2) as "Filmtitel",
              date_format(mov_premiere, \'%Y\') as "Jahr", 
              concat_ws(\' \', per_fname, per_secName, per_lname) as "Regie" 
              from movie.genre g natural join movie.movie m natural join movie.movie_director md natural join movie.person p
              order by Genre, Filmtitel, Jahr';
$query4 = 'select Genre, Filmtitel, Jahr, Regie from tblpremiere order by Genre, Filmtitel, Jahr';
try
{
    $stmt = $con->prepare($query);
    $stmt->execute();
    $stmt1 = $con->prepare($query1);
    $stmt1->execute();
    $stmt2 = $con->prepare($query2);
    $stmt2->execute();
    $stmt3 = $con->prepare($query3);
    $stmt3->execute();

    $stmtinsertMovie = $con->prepare($insertMovie);
    $stmtinsertMovie->execute();
    $stmt4 = $con->prepare($query4);
    $stmt4->execute();
    //Hier wird nur die Suche gemacht
    echo '<form method="post">';
    echo '<div class="form-group">
          <label style="font-weight: bold">Suche:</label>
          <input class="inputbox input-field" type="text" style="width: 25em" name="suchfeld" placeholder="Sie können in jeder Spalte suchen"></div>';



    //Hier fange ich mit dem Table an, was es alles in der Datenbank gibt!!! --Alles an Ausgabe
    echo '<div class="table">
          <div class="row">';
    for($i = 0; $i < $stmt4->columnCount(); $i++)
    {
        //Hier wird dann mit Name von den Metadaten abgegriffen somit ist der Name der Spaltenname
        echo '<div class="col font-weight-bold">'.$stmt4->getColumnMeta($i)['name'].'</div>';
    }
    echo '<div class="col"></div>';
    echo '</div>'; // row end --Hier wird dann die Überschrift beendet
    $lastID = 0;
    while($row = $stmt4->fetch(PDO::FETCH_NUM))
    {
        echo '<div class="row">';
            //Wenn ein Filmtitel gleich ist wie das Vorherige
            if (strcmp($lastID, $row[1]) == 0) {
                echo '<div class="col"></div><div class="col"></div><div class="col"></div><div class="col">und ' . $row[3] . '</div>';
            } else {
                //Spalten, die ich ausgeben möchte
                echo '<div class="col">' . $row[0] .'</div>
                      <div class="col">' . $row[1] . '</div>
                      <div class="col">' . $row[2] . '</div>
                      <div class="col">' . $row[3] . '</div>';
            }
            //LastID wieder mit dem aktuellen Set füllen und wieder gefüllt bin
            $lastID = $row[1];
        echo '</div>';
    }
    echo '</div>'; // Table end

    //Submit Button
    echo '<div class="form-group">
    <label class="col-md-2"></label>
    <input type="submit" style="width: 25em" name="search" value="Suche starten"></div>';
    echo '</form>';
























//Hier wird dann alles ausgegeben, was speziell meine Suche betrifft, wenn ich etwas eingegeben habe!!!
    if(isset($_POST['search']))
    {
        //Das ist die Suche oben
        $suchfeld = $_POST['suchfeld'];
        echo '<h2>Ergebnis der Suche</h2>';
        $query6 = 'select Genre, Filmtitel, Jahr, Regie from tblpremiere where genre like ? or filmtitel like ? or jahr like ? or regie like ?';

        $stmt6 = $con->prepare($query6);
        $suche = '%'.$suchfeld.'%';
        $stmt6->execute([$suche, $suche, $suche, $suche]);
        echo '<div class="table">
              <div class="row">';
        //Überschrifen
        for($i = 0; $i < $stmt6->columnCount(); $i++)
        {
            echo '<div class="col font-weight-bold">'.$stmt6->getColumnMeta($i)['name'].'</div>';
        }
        echo '</div>';
        //Datensätze
        while($row = $stmt6->fetch(PDO::FETCH_NUM))
        {
            echo '<div class="row">';
            foreach($row as $r)
            {
                echo '<div class="col">'.$r.'</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
} catch(Exception $e)
{
    echo $e->getCode().': '.$e->getMessage();
}