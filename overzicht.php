<html>
	<head>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">	
		<script src="http://code.jquery.com/jquery-1.12.0.min.js"></script>
				<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	</head>
	<body>
<?php
/*
error_reporting(-1);
ini_set('display_errors', 'On');
*/
	mysql_connect("localhost", "u64368p146036_voetbal", "3plead-Underdog2-ludicrous");
	mysql_select_db("u64368p146036_voetbal");
	
	$query_dates = mysql_query("SELECT datum FROM wedstrijd WHERE datum != '0000-00-00' GROUP BY datum ORDER BY datum DESC");
	$query_dates2 = mysql_query("SELECT w.datum as wdatum, u.datum as udatum FROM wedstrijd as w, uitslag as u WHERE w.datum != '0000-00-00' AND w.datum = u.datum GROUP BY w.datum ORDER BY w.datum DESC");
	$query_spelers = mysql_query("SELECT s.id, s.naam, s.actief FROM spelers as s WHERE s.actief = '1' ORDER BY s.naam");
	$query_spelers2 = mysql_query("SELECT s.id FROM spelers as s");		
	
	$row_aantal_team1_win = mysql_fetch_assoc(mysql_query("select count(*) as aantal from uitslag WHERE voor > tegen"));
	$row_aantal_team2_win = mysql_fetch_assoc(mysql_query("select count(*) as aantal from uitslag WHERE voor < tegen"));
	$row_uitslag_gelijk = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as gelijk FROM uitslag WHERE voor = tegen"));
	$row_aantal_uitslagen = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as aantal FROM uitslag"));
	$row_aantal_wedstrijden = mysql_num_rows(mysql_query("SELECT datum FROM wedstrijd GROUP BY datum"));

	$print = null; 
	$array_datum_spelers = array();
	
	print "<H3><a href='http://jverhoeff.com/voetbal0101/voetbal001.php'>Invoeren</a></H3>";
	print "<H3><a href='http://jverhoeff.com/voetbal0101/spelers.php'>Spelers</a></H3>";
	print "<H3><a href='http://jverhoeff.com/voetbal0101/overzicht.php'>Overzicht</a></H3><br>";
	
	print "<H3>Bereken hoe vaak je bij een persoon in een team gezeten hebt</H3>";
	print "<form action='#' method='POST'>";
	print "<table border='0' cellpadding='5' cellspacing='0' width='50%'>";
	print "<tr><td>Speler:<select id='selectplayer' name='speler1'><option value='0'>-</option>";
	while($rows_query_spelers = mysql_fetch_array($query_spelers)) {
		print "<option value='".$rows_query_spelers['id']."'>".$rows_query_spelers['naam']."</option>";
	}
	print "</select></td></tr>";
	print "</table>";
	print "</form>";
	
	if($_POST['speler1'] != 0) {
		$post_speler1 = $_POST['speler1'];	
		
		while($rows_query_dates2 = mysql_fetch_array($query_dates2)) {
			$this_date = $rows_query_dates2['wdatum'];
			for($x=1; $x<3; $x++){
				$query_spelers_1_all = mysql_query("SELECT w.speler_id, w.datum, w.team, s.id, s.naam FROM wedstrijd as w, spelers as s WHERE w.datum = '$this_date' AND speler_id = '$post_speler1' AND w.team = '$x' AND w.speler_id = s.id");
				
				if (mysql_num_rows($query_spelers_1_all)!=0) { 
					
					$query_spelers2 = mysql_query("SELECT w.speler_id, w.datum, w.team, s.naam as naam FROM wedstrijd as w, spelers as s WHERE w.datum = '$this_date' AND w.team = '$x' AND speler_id != '$post_speler1' AND w.speler_id = s.id");								
					while($rows_query_spelers2 = mysql_fetch_array($query_spelers2)) {
						$speler_datum = $rows_query_spelers2['datum'];
						$speler_naam = $rows_query_spelers2['naam'];	
						$speler_team = $rows_query_spelers2['team'];
						$speler_id = $rows_query_spelers2['speler_id'];
						$array_datum_spelers[] = $speler_naam;	
						$array_spelers[] = array($speler_id, $speler_naam, $speler_datum, $speler_team, $x);	
					}
				}
			}		
		}
		$occurences = array_count_values($array_datum_spelers);
		
		
		$query_posted_speler = mysql_query("SELECT w.speler_id, s.naam FROM wedstrijd as w, spelers as s WHERE speler_id = '$post_speler1' AND w.speler_id = s.id LIMIT 1");
		$fetch_posted_speler = mysql_fetch_row($query_posted_speler);
		print "<H3>".$fetch_posted_speler[1]."</H3>";
		print "<table border='0' cellpadding='5' cellspacing='0' width='50%'>";		
		
		foreach($occurences as $speler => $spelerinfo){
	
			print "<td>".$spelerinfo."x</td><td>".$speler."</td></tr>";
		}
		print "</table>";
	}
		echo '<hr>';	
	

	while($rows_query = mysql_fetch_array($query_dates)) {
		$datum = $rows_query['datum'];
		$query_spelers_1 = mysql_query("SELECT w.speler_id, w.datum, w.team, w.score, s.id, s.naam FROM wedstrijd as w, spelers as s WHERE w.datum = '$datum' AND w.team = '1' AND w.speler_id = s.id");
		$query_spelers_2 = mysql_query("SELECT w.speler_id, w.datum, w.team, w.score, s.id, s.naam FROM wedstrijd as w, spelers as s WHERE w.datum = '$datum' AND w.team = '2' AND w.speler_id = s.id");
		$query_uitslag = mysql_query("SELECT u.datum, u.voor, u.tegen, w.datum FROM uitslag as u, wedstrijd as w WHERE w.datum = '$datum' AND u.datum = w.datum");
		$row_uislag = mysql_fetch_assoc($query_uitslag);
					
		$print .= "<table border='1' border-color='black' cellpadding='5' cellspacing='0' width='100%'>";
		$print .= "<tr>";
		$print .= "<td width='20%'>".$datum."</td><td width='40%'>Team1</td><td width='40%'>Team2</td>";
		$print .= "</tr>";
				
		$print .= "<tr>";
        $print .= "<td>&nbsp;</td>";
        
        $print .= "<td>";
        while($rows_spelers_1 = mysql_fetch_array($query_spelers_1)) {
       		$print .= $rows_spelers_1['naam']." - ".$rows_spelers_1['score']."<br />";  
       		$totaal_team1 = $totaal_team1 + $rows_spelers_1['score'];
        }	
        $print .= "</td>";
        
        
        $print .= "<td>";
        while($rows_spelers_2 = mysql_fetch_array($query_spelers_2)) {
       		$print .= $rows_spelers_2['naam']." - ".$rows_spelers_2['score']."<br />";  
       		$totaal_team2 = $totaal_team2 + $rows_spelers_2['score'];     
        }	
        $print .= "</td>";		        
		
		$print .= "</tr>";
		
		$print .= "<tr>";
		$print .= "<td>Teamscore</td>";
		$print .= "<td>".$totaal_team1."</td>";
		$print .= "<td>".$totaal_team2."</td>";
		$print .= "</tr>";
		
		$print .= "<tr>";
		$print .= "<td>Eindstand</td>";
		if(!empty($row_uislag['voor'])){
			$print .= "<td>".$row_uislag['voor']."</td>";
			$print .= "<td>".$row_uislag['tegen']."</td>";
		}
		else{
			$print .= "<td><font color='red'>Niet ingevuld!</font></td>";
			$print .= "<td><font color='red'>Niet ingevuld!</font></td>";	
		}		
		$print .= "</tr>";
		
		$print .= "<table>";
		$print .= "<br />";
		$totaal_team1 = null;
		$totaal_team2 = null;
	}
	
	print "<br />";
	print "<H3>Overige statistiekjes!</H3>";
	print "Totaal ".$row_aantal_wedstrijden." wedstrijden gegenereerd.";
	print "<br/>";
	print "Totaal ".$row_aantal_uitslagen['aantal']." uitslagen ingevuld.";
	print "<br/>";
	print "Team 1 heeft ".$row_aantal_team1_win['aantal']." keer gewonnen";
	print "<br/>";
	print "Team 2 heeft ".$row_aantal_team2_win['aantal']." keer gewonnen";
	print "<br/>";
	print "Er is ".$row_uitslag_gelijk['gelijk']." keer gelijk gespeeld";
	echo '<hr>';	
	
	print "<br />";
	print "<H3>Gespeelde wedstrijden!</H3>";	
	print $print;
		
		
?>

<script> $('#selectplayer').change(function(){  if($(this).val() != '#'){ $(this).closest('form').submit(); } }); </script>
	</body>
</html>