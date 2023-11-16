<?php

/*
error_reporting(-1);
ini_set('display_errors', 'On');
*/

//echo date("H:m:s");
?>
<html>
    <head>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<!--         <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/redmond/jquery-ui.css"> -->
	   <script src="//code.jquery.com/jquery-1.10.2.js"></script>
<!-- 	    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> -->
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	    <script>
        $(function () {
            $("#datepicker").datepicker({dateFormat: 'yy-mm-dd'});

        });
        $(function () {
            $("#datepicker2").datepicker({dateFormat: 'yy-mm-dd'});

        });
        $(function () {
            $("#datepicker3").datepicker({dateFormat: 'yy-mm-dd'});

        });
    </script>
<style>
			div.blueTable {
			  border: 1px solid #1C6EA4;
			  background-color: #EEEEEE;
			  width: 80%;
			  text-align: left;
			  border-collapse: collapse;
			}
			.divTable.blueTable .divTableCell, .divTable.blueTable .divTableHead {
			  border: 1px solid #AAAAAA;
			  padding: 3px 2px;
			}
			.divTable.blueTable .divTableBody .divTableCell {
			  font-size: 13px;
			}
			.divTable.blueTable .divTableRow:nth-child(even) {
			  background: #D0E4F5;
			}
			.divTable.blueTable .divTableHeading {
			  background: #1C6EA4;
			  background: -moz-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
			  background: -webkit-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
			  background: linear-gradient(to bottom, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
			  border-bottom: 2px solid #444444;
			}
			.divTable.blueTable .divTableHeading .divTableHead {
			  font-size: 15px;
			  font-weight: bold;
			  color: #FFFFFF;
			  border-left: 2px solid #D0E4F5;
			}
			.divTable.blueTable .divTableHeading .divTableHead:first-child {
			  border-left: none;
			}

			.blueTable .tableFootStyle {
			  font-size: 14px;
			  font-weight: bold;
			  color: #FFFFFF;
			  background: #D0E4F5;
			  background: -moz-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
			  background: -webkit-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
			  background: linear-gradient(to bottom, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
			  border-top: 2px solid #444444;
			}
			.blueTable .tableFootStyle {
			  font-size: 14px;
			}
			.blueTable .tableFootStyle .links {
				 text-align: right;
			}
			.blueTable .tableFootStyle .links a{
			  display: inline-block;
			  background: #1C6EA4;
			  color: #FFFFFF;
			  padding: 2px 8px;
			  border-radius: 5px;
			}
			.blueTable.outerTableFooter {
			  border-top: none;
			}
			.blueTable.outerTableFooter .tableFootStyle {
			  padding: 3px 5px;
			}
			/* DivTable.com */
			.divTable{ display: table; }
			.divTableRow { display: table-row; }
			.divTableHeading { display: table-header-group;}
			.divTableCell, .divTableHead { display: table-cell;}
			.divTableHeading { display: table-header-group;}
			.divTableFoot { display: table-footer-group;}
			.divTableBody { display: table-row-group;}

input[type=checkbox] {
    transform: scale(2);
    -ms-transform: scale(2);
    -webkit-transform: scale(2);
    padding: 10px;
}
		</style>


    </head>
    <body>
        <?php


$sKey = 'voetbal2013';
$rCon = mysqli_connect("localhost", "u64368p146036_voetbal", "3plead-Underdog2-ludicrous", "u64368p146036_voetbal");


if(!isset($_GET['date'])){
	print "<H3><a href='http://jverhoeff.com/voetbal0101/voetbal001.php'>Invoeren</a></H3>";
	print "<H3><a href='http://jverhoeff.com/voetbal0101/spelers.php'>Spelers</a></H3>";
	print "<H3><a href='http://jverhoeff.com/voetbal0101/overzicht.php'>Overzicht</a></H3><br>";
}
// Check connection
echo '<form method="post" action="">';




if (mysqli_connect_errno($rCon)) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}





//STUUR CIJFERMAILS: ALS OP DEKNOP GEDRUKT IS OM MAILS TE VERSTUREN OM DE SCORE IN TE VULLEN
elseif (isset($_POST['cijferdate']) && $_POST['cijferdate'] != '' && $_POST['cijferdate'] != '0') {
	$sQuery = '
    INSERT INTO `uitslag` (
    `datum`,
    `voor`,
    `tegen`
    )
    VALUES (
    "' . $_POST['cijferdate'] . '", "' . $_POST['cijfer1'] . '", "' . $_POST['cijfer2'] . '"
    )';
	$rResult = mysqli_query($rCon, $sQuery);

	$sQuery = 'SELECT s.id, s.email, s.naam FROM spelers s, wedstrijd w WHERE email != "" AND datum = "' . $_POST['cijferdate'] . '" AND s.id = w.speler_id';
	$rResult = mysqli_query($rCon, $sQuery);

	while ($rRow = mysqli_fetch_assoc($rResult)) {
		$aArray[] = array($rRow['id'], $rRow['email'], $rRow['naam']);
	}

	for($aantal_mails=0; $aantal_mails<3; $aantal_mails++){
		$iID = rand(0, (count($aArray) - 1));

		$sMail = '
Dag ' . $aArray[$iID][2] . ',

Zou jij cijfers willen geven voor afgelopen vrijdag.

http://jverhoeff.com/voetbal0101/cijfers_geven.php?date=' . base64_encode($_POST['cijferdate']) . '&speler=' . $aArray[$iID][0] . '

Deze gegevens worden gebruikt om de partijtjes nog evenwichtiger te maken.

Let op: Vanaf nu graag cijfers tussen 5 en 10 geven.

Gr. de foeballers
';

		$Name = "Vrijdag Voetbal"; //senders name
		$email = "voetbal@jverhoeff.com"; //senders e-mail adress
		//$recipient = "jverhoeff@gmail.com"; //recipient
		$recipient = $aArray[$iID][1]; //recipient
		$mail_body = $sMail; //mail body
		$subject = "Cijfers voetbalwedstrijdje ". $_GET['date']; //subject
		$header = "From: " . $Name . " <" . $email . ">\r\n"; //optional headerfields

		mail($recipient, $subject, $mail_body, $header); //mail command :)
		echo 'De mail is verstuurd naar ' . $aArray[$iID][2].'<br/>';
	}
}


//VERWIJDER TEAMS/WEDSTRIJD
elseif (isset($_POST['deldate']) && $_POST['deldate'] != '') {
	$sQuery = 'DELETE FROM wedstrijd WHERE datum = "' . $_POST['deldate'] . '"';
	$rResult = mysqli_query($rCon, $sQuery);

	echo 'De teams zijn verwijderd';
}


//SPELERSLIJST MET SCORES BEKIJKEN!
elseif (isset($_GET['spelers'])) {
	$sQuery = 'SELECT * FROM spelers s, cijfer c, wedstrijd w, uitslag u WHERE s.id = c.speler_id AND (c.datum = w.datum AND w.speler_id = s.id) AND w.datum = u.datum';
	$rResult = mysqli_query($rCon, $sQuery);

	while ($rRow = mysqli_fetch_assoc($rResult)) {
		$aCijfer[$rRow['id']]['score'] = $rRow['score'];

		if ($rRow['team'] == 1) {
			$fCoefficient = 1 + ($rRow['voor'] / 100);
		} else {
			$fCoefficient = 1 + ($rRow['tegen'] / 100);
		}

		$aCijfer[$rRow['id']]['nr'] = $rRow['id'];
		$aCijfer[$rRow['id']]['email'] = $rRow['email'];
		$aCijfer[$rRow['id']]['naam'] = $rRow['naam'];
		$aCijfer[$rRow['id']]['cijfer'] += $rRow['cijfer'] * $fCoefficient;
		$aCijfer[$rRow['id']]['count'] += 1;
		$aCijfer[$rRow['id']]['type'] = $rRow['type'];
		
	}

	$sQuery = 'SELECT * FROM spelers WHERE id NOT IN (SELECT s.id FROM spelers s, cijfer c, wedstrijd w, uitslag u WHERE s.id = c.speler_id AND (c.datum = w.datum AND w.speler_id = s.id) AND w.datum = u.datum)';
	$rResult = mysqli_query($rCon, $sQuery);

	while ($rRow = mysqli_fetch_assoc($rResult)) {
		$aCijfer[$rRow['id']]['nr'] = $rRow['id'];
		$aCijfer[$rRow['id']]['email'] = $rRow['email'];
		$aCijfer[$rRow['id']]['naam'] = $rRow['naam'];
		$aCijfer[$rRow['id']]['score'] = $rRow['score'];
		$aCijfer[$rRow['id']]['cijfer'] = $rRow['score'];
		$aCijfer[$rRow['id']]['count'] = 1;
		$aCijfer[$rRow['id']]['type'] = $rRow['type'];
	}

	foreach ($aCijfer as $iId => $aPlayer) {
		$aCijfer[$iId]['total'] = round(($aPlayer['cijfer'] + $aPlayer['score']) / ($aPlayer['count'] + 1), 2);
	}
	sortArray($aCijfer, 'total');

	echo '<table>';
	echo '<tr><td width="150"><b>naam</b></td><td width="75"><b>cijfer</b></td><td width="75"><b>type</b></td>';
	foreach ($aCijfer as $iId => $aPlayer) {
		echo '<tr><td>(' . $aPlayer['nr'] . ')' . $aPlayer['naam'] . '</td><td>' . number_format($aPlayer['total'], 2) . '</td><td>' . $aPlayer['type'] . '</td></tr>';
	}
	echo '</table>';
}


else {

	if (count($_POST) > 0){
		// Check voor nieuwe spelers
		for ($iPlayer = 1; $iPlayer <= 3; $iPlayer++) {
			if ($_POST['newplayer' . $iPlayer]['naam'] != '') {
				mysqli_query($rCon, 'INSERT INTO spelers (naam, email, score, type) VALUES ("' . $_POST['newplayer' . $iPlayer]['naam'] . '", "' . $_POST['newplayer' . $iPlayer]['email'] . '", "' . str_replace(',', '.', $_POST['newplayer' . $iPlayer]['score']) . '", "' . $_POST['newplayer' . $iPlayer]['type'] . '")');
				$_POST['player'][mysqli_insert_id($rCon)] = 'on';
			}
		}

		//HAAL ALLE ID'S OP VAN DE SPELERS DIE GESELECTEERD ZIJN
		foreach ($_POST['player'] as $sId => $sValue) {

			$sQuery = 'SELECT DISTINCT w.datum as wdatum, w.id, 
			s.email as email,s.naam as naam, s.score as score,s.type as type,
			c.speler_id as speler_id,
			u.voor as voor,u.tegen as tegen,c.cijfer as cijfer 
			FROM spelers s, cijfer c, wedstrijd w, uitslag u 
			WHERE s.id = c.speler_id 
			AND (c.datum = w.datum AND w.speler_id = s.id) 
			AND w.datum = u.datum 
			AND s.id = '. $sId.'
			ORDER BY w.datum 
			DESC LIMIT 25';
			
			
			$rResult = mysqli_query($rCon, $sQuery);


			//ALS SPELER NOG NIET EERDER MEEGESPEELD HEEFT, HAAL DAN ZIJN BASISSCORE OP
			if (mysqli_num_rows($rResult) == 0) {

				$sQuery = 'SELECT * FROM spelers WHERE id = ' . $sId;
				$rResult = mysqli_query($rCon, $sQuery);

				$rRow = mysqli_fetch_assoc($rResult);

				$aCijfer[$rRow['id']]['email'] = $rRow['email'];
				$aCijfer[$rRow['id']]['naam'] = $rRow['naam'];
				$aCijfer[$rRow['id']]['score'] = $rRow['score'];
				$aCijfer[$rRow['id']]['cijfer'] = $rRow['score'];
				$aCijfer[$rRow['id']]['count'] = 1;
				$aCijfer[$rRow['id']]['type'] = $rRow['type'];

			//HEEFT DE SPELER AL EERDER MEEGEDAAN, WORDT ZIJN CIJFER BEREKEND OP BASIS VAN ZIJN HISTORIE. PER WEDSTRIJD WORDT ER VOLGENDE BEREKEND
			
			} else {
				while ($rRow = mysqli_fetch_assoc($rResult)) {
					//VOOR ELKE WEDSTRIJD DIE AL MEEGESPEELD IS, WORDT ZIJN STARTSCORE GEPAKT
					$aCijfer[$rRow['speler_id']]['score'] = $rRow['score'];
					
					//HET AANTAL GESPEELDE PUNTEN VAN DEZE WEDSTRIJD / 100 + 1. DUS HEEFT DE SPELER GEWONNEN OF VERLOREN MET 6 PUNTEN, IS HET 6/100 EN DAN afhankelijk of je gewonnen of verloren hebt erbij of eraf MET 1 = 1,06
					if ($rRow['team'] == 1) {
						$fCoefficient = 1 + ($rRow['voor'] / 100);
					} else {
						$fCoefficient = 1 + ($rRow['tegen'] / 100); //1,06
					}
					
					$aCijfer[$rRow['speler_id']]['email'] = $rRow['email'];
					$aCijfer[$rRow['speler_id']]['naam'] = $rRow['naam'];
					
					//HUIDIGE CIJFER IS nog startscore. Na elke wedstrijd is het het vorige behaalde cijfer * hierboven berekende.
					$aCijfer[$rRow['speler_id']]['cijfer'] += $rRow['cijfer'] * $fCoefficient;
					$totstandkoming_cijfers .= $aCijfer[$rRow['speler_id']]['naam'] . ": " . $aCijfer[$rRow['speler_id']]['cijfer'] . " - " . round( ($aCijfer[$rRow['speler_id']]['cijfer'] + $rRow['score']) / ($aCijfer[$rRow['speler_id']]['count']+2), 3) . "<br>";
					
					//HET AANTAL GESPEELDE WEDSTRIJDEN:
					$aCijfer[$rRow['speler_id']]['count'] += 1;
					$aCijfer[$rRow['speler_id']]['type'] = $rRow['type'];


				}
				$totstandkoming_cijfers .= "<br>";
			}
		}


		$iId = 0;

		foreach ($aCijfer as $sId => $aProperties) {
			$aTeamBuilder[$iId]['email'] = $aProperties['email'];
			$aTeamBuilder[$iId]['naam'] = $aProperties['naam'];
			$aTeamBuilder[$iId]['id'] = $sId;
			
			$aTeamBuilder[$iId]['cijfer'] = round(($aProperties['cijfer'] + $aProperties['score']) / ($aProperties['count'] + 1), 2);
			$aTeamBuilder[$iId++]['type'] = $aProperties['type'];
		}

		sortArray($aTeamBuilder, 'cijfer');

		foreach ($aTeamBuilder as $aPlayer) {
			switch ($aPlayer['type']) {
			case 'A':
				$aPlayerA[] = $aPlayer;
				break;
			case 'B':
				$aPlayerB[] = $aPlayer;
				break;
			case 'D':
				$aPlayerD[] = $aPlayer;
				break;
			}
		}

		if (count($aPlayerA) % 2 != 0) {
			$iIndex = (count($aPlayerB) - 1);
			if ($aPlayerB[$iIndex]) {
				$aPlayerA[] = $aPlayerB[$iIndex];
				unset($aPlayerB[$iIndex]);
			}
		}

		if (count($aPlayerD) % 2 != 0) {
			$iIndex = (count($aPlayerB) - 1);
			if ($aPlayerB[$iIndex]) {
				$aPlayerD[] = $aPlayerB[$iIndex];
				unset($aPlayerB[$iIndex]);
			}
		}

		if (count($aPlayerA)) {
			sortArray($aPlayerA, 'cijfer');
		}
		if (count($aPlayerB)) {
			sortArray($aPlayerB, 'cijfer');
		}
		if (count($aPlayerD)) {
			sortArray($aPlayerD, 'cijfer');
		}

		$iCounterA = 1;
		foreach ($aPlayerA as $aPlayer) {
			if ($iCounterA == 1) {
				$aAttackD[] = $aPlayer;
			} elseif ($iCounterA == 2 || $iCounterA == 3) {
				$aAttackE[] = $aPlayer;
			}
			if ($iCounterA >= 4) {
				if ($iCounterA % 2) {
					$aAttackE[] = $aPlayer;
				} else {
					$aAttackD[] = $aPlayer;
				}
			}
			$iCounterA++;
		}

		foreach ($aAttackD as $aPlayer) {
			$iDCijfer += $aPlayer['cijfer'];
		}

		foreach ($aAttackE as $aPlayer) {
			$iECijfer += $aPlayer['cijfer'];
		}

		$iCounterD = 1;
		foreach ($aPlayerD as $aPlayer) {
			if ($iCounterD == 1) {
				$aDefendF[] = $aPlayer;
			} elseif ($iCounterD == 2 || $iCounterD == 3) {
				$aDefendG[] = $aPlayer;
			}
			if ($iCounterD >= 4) {
				if ($iCounterD % 2) {
					$aDefendG[] = $aPlayer;
				} else {
					$aDefendF[] = $aPlayer;
				}
			}
			$iCounterD++;
		}

		foreach ($aDefendF as $aPlayer) {
			$iFCijfer += $aPlayer['cijfer'];
		}

		foreach ($aDefendG as $aPlayer) {
			$iGCijfer += $aPlayer['cijfer'];
		}

		if (count($aPlayerB)) {
			$iCounterB = 1;
			foreach ($aPlayerB as $aPlayer) {
				if ($iCounterB == 1) {
					$aBothH[] = $aPlayer;
				} elseif ($iCounterB == 2 || $iCounterB == 3) {
					$aBothI[] = $aPlayer;
				}
				if ($iCounterB >= 4) {
					if ($iCounterB % 2) {
						$aBothI[] = $aPlayer;
					} else {
						$aBothH[] = $aPlayer;
					}
				}
				$iCounterB++;
			}

			foreach ($aBothH as $aPlayer) {
				$iHCijfer += $aPlayer['cijfer'];
			}

			foreach ($aBothH as $aPlayer) {
				$iICijfer += $aPlayer['cijfer'];
			}
		}

		$aTeam1 = $aAttackD;
		$aTeam2 = $aAttackE;

		if ($iDCijfer < $iECijfer) {
			if ($iFCijfer < $iGCijfer) {
				$aTeam1 = array_merge($aAttackD, $aDefendG);
				$aTeam2 = array_merge($aAttackE, $aDefendF);
			} else {
				$aTeam1 = array_merge($aAttackD, $aDefendF);
				$aTeam2 = array_merge($aAttackE, $aDefendG);
			}
		} else {
			if ($iFCijfer < $iGCijfer) {
				$aTeam1 = array_merge($aAttackD, $aDefendF);
				$aTeam2 = array_merge($aAttackE, $aDefendG);
			} else {
				$aTeam1 = array_merge($aAttackD, $aDefendG);
				$aTeam2 = array_merge($aAttackE, $aDefendF);
			}
		}

		if ($iTeam1Cijfer < $iTeam2Cijfer) {
			if ($iHCijfer < $iICijfer) {
				if (count($aBothI)) {
					$aTeam1 = array_merge($aTeam1, $aBothI);
				}
				if (count($aBothH)) {
					$aTeam2 = array_merge($aTeam2, $aBothH);
				}
			} else {
				if (count($aBothH)) {
					$aTeam1 = array_merge($aTeam1, $aBothH);
				}
				if (count($aBothI)) {
					$aTeam2 = array_merge($aTeam2, $aBothI);
				}
			}
		} else {
			if ($iHCijfer < $iICijfer) {
				if (count($aBothH)) {
					$aTeam1 = array_merge($aTeam1, $aBothH);
				}
				if (count($aBothI)) {
					$aTeam2 = array_merge($aTeam2, $aBothI);
				}
			} else {
				if (count($aBothI)) {
					$aTeam1 = array_merge($aTeam1, $aBothI);
				}
				if (count($aBothH)) {
					$aTeam2 = array_merge($aTeam2, $aBothH);
				}
			}
		}

		$sView = '<h3>Team 1</h3>';
		foreach ($aTeam1 as $aPlayer) {
			$speler_score = $aPlayer['cijfer'];
			mysqli_query($rCon, 'INSERT INTO wedstrijd (datum, speler_id, team, score) VALUES ("' . $_POST['datum'] . '", "' . $aPlayer['id'] . '", 1, "'.$aPlayer['cijfer'].'")');
			$iTeam1Cijfer += $aPlayer['cijfer'];
			$sView .= '(' . $aPlayer['type'] . ') ' . $aPlayer['naam'] . '<span class="cijferdiv"> - ' .$aPlayer['cijfer']. '</span><br/>';

		}
		$sView .= '<b>score: ' . number_format($iTeam1Cijfer, 2) . '</b><br><br>';

		$sView .= '<h3>Team 2</h3>';
		foreach ($aTeam2 as $aPlayer) {
			mysqli_query($rCon, 'INSERT INTO wedstrijd (datum, speler_id, team, score) VALUES ("' . $_POST['datum'] . '", "' . $aPlayer['id'] . '", 2, "'.$aPlayer['cijfer'].'")');
			$iTeam2Cijfer += $aPlayer['cijfer'];
			$sView .= '(' . $aPlayer['type'] . ') ' . $aPlayer['naam'] . '<span class="cijferdiv"> - ' .$aPlayer['cijfer']. '</span><br/>';
		}
		$sView .= '<b>score: ' . number_format($iTeam2Cijfer, 2) . '</b>';

		echo $sView;
		
		echo "<br><br><span id='btnHideCijfers'>Verberg cijfers door hier te klikken</span>";
		
		print "<br><br><br><br><br><br><br><br><H4>Totstand koming cijfers:</H4>";
		print "Naam: Tussentijds: Cijfer+Score / Aantalwedstrijden<br>";
		print $totstandkoming_cijfers;
	}


	else {
		echo '<h3>Verwijder teams/Wedstrijd!</h3>';
		echo 'Verwijder de teams van <input type="text" name="deldate" id="datepicker" style="width:120px">';
		echo '<br/><input type="submit" value="verwijder">';
		echo '<hr>';


		//SELECT w.* FROM wedstrijd w NATURAL LEFT JOIN uitslag u WHERE u.datum IS NULL
		//$DateGame_Query = "SELECT u.datum as udatum, w.datum as wdatum FROM wedstrijd w, uitslag u WHERE w.datum != u.datum AND u.datum != '0000-00-00'";
		$DateGame_Query = "SELECT w.* FROM wedstrijd w NATURAL LEFT JOIN uitslag u WHERE u.datum IS NULL GROUP BY w.datum ORDER BY w.datum DESC";
		$query_dates_games = mysqli_query($rCon, $DateGame_Query);

		echo '<h3>Stuur cijfermails!</h3>';
		//echo 'Stuur cijfermails voor <input type="text" name="cijferdate" id="datepicker2" style="width:120px"><br/>';
		echo 'Stuur cijfermails voor <select id="selectgame" name="cijferdate"><option value="0">-</option>';
		while($rows_query_games = mysqli_fetch_array($query_dates_games)) {
			echo '<option value="'.$rows_query_games["datum"].'">'.$rows_query_games["datum"].'</option>';
		}
		echo "</select><br/>";
		echo 'Uitslag (team1 - team2): <input type="text" name="cijfer1" style="width:25px"> - <input type="text" name="cijfer2" style="width:25px">';
		echo '<br/><input type="submit" value="stuur mails">';
		echo '<hr>';




		echo '<h3>Teams maken!</h3>';
		echo 'Datum <input type="text" name="datum" id="datepicker3" style="width:120px">';
		echo '<br/>Selecteer wie er mee doen (als er nieuwe spelers meedoen dan even naam en type speler invullen)<br/><br/>';
		$sQuery = 'SELECT * FROM spelers s WHERE actief="1" ORDER BY naam';
		$rResult = mysqli_query($rCon, $sQuery);

		echo '<div></div>';
		echo '<br/>';


		while ($rRow = mysqli_fetch_assoc($rResult)) {
			echo '<input type="checkbox" name="player[' . $rRow['id'] . ']">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $rRow['naam'] . '<br/><br/>';
		}


		echo '<br>';

		echo '<div></div>';
?>


        <script>

            var countChecked = function () {
                var s = $("input:checked").length;
                $("div").text(s + (s === 1 ? " speler" : " spelers") + " geselecteerd!");
            };

            countChecked();

            $("input[type=checkbox]").on("click", countChecked);

        </script>

                <?php
		echo '<h4>Nieuwe spelers</h4>';
		echo 'Naam: <input name="newplayer1[naam]"> Email: <input name="newplayer1[email]"> Startcijfer (5-10): <input name="newplayer1[score]" style="width:25px"> Type: <select name="newplayer1[type]"><option value="A">Aanvaller</option><option value="D">Verdediger</option><option value="B">Allebei</option></select><br/><br/>';
		echo 'Naam: <input name="newplayer2[naam]"> Email: <input name="newplayer2[email]"> Startcijfer (5-10): <input name="newplayer2[score]" style="width:25px"> Type: <select name="newplayer2[type]"><option value="A">Aanvaller</option><option value="D">Verdediger</option><option value="B">Allebei</option></select><br/><br/>';
		echo 'Naam: <input name="newplayer3[naam]"> Email: <input name="newplayer3[email]"> Startcijfer (5-10): <input name="newplayer3[score]" style="width:25px"> Type: <select name="newplayer3[type]"><option value="A">Aanvaller</option><option value="D">Verdediger</option><option value="B">Allebei</option></select><br/><br/>';

		echo '<br/><br/><input type="submit" value="Genereer teams">';
	}
}





echo '</form>';

function sortArray(&$aToSort, $sSortBy) {
	$aSort = array();

	foreach ($aToSort as $aPlayer) {
		foreach ($aPlayer as $sKey => $sValue) {
			if (!isset($aSort[$sKey])) {
				$aSort[$sKey] = array();
			}
			$aSort[$sKey][] = $sValue;
		}
	}

	return array_multisort($aSort[$sSortBy], SORT_DESC, $aToSort);
}
?>

<script>
	    $("#btnHideCijfers").click(function(){
			$(".cijferdiv").toggle();
		});
	
</script>
    </body>
</html>