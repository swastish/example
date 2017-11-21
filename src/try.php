<?php
include 'connectDB.php';
include 'PdfBox\PdfBox.php';
//extractPdf('2017-0916-comm-2017-sept-16-17-2.pdf','11/30/2017');
//extractPdf('2017-0514-dwny-br-june-2017.pdf','11/30/2017');
//extractPdf('2017-0416-dwny-may-2017-lc-meet.pdf','11/30/2017');
//extractPdf('2017-0115-Jan.-2017 Commerce meet.pdf','11/30/2017');
extractPdf('BellFlower-Meet.pdf','11/30/2017');
function extractPdf($pdfName, $deadline){
$splitDeadlineArr = explode("/",$deadline);
$month = $splitDeadlineArr[0];
$date = $splitDeadlineArr[1];
$year = $splitDeadlineArr[2];
$deadlineFormatted = $year."-".$month."-".$date;
$GLOBALS['meet_deadline'] = $deadlineFormatted;	
$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$pdf = '..\\PDFs\\'.$pdfName;
$converter = new PdfBox;
$converter->setPathToPdfBox('..\Jars\pdfbox-app-2.0.7.jar');
$pageNumber = 1;
$step=1;	
$firstPage = trim($converter->textFromPdfFile($pdf, $pageNumber));
//Dates of Meet,per event charge, individual swimmig charge and payee information will be extracted from first page of PDF
$rows = explode("\n", $firstPage);
foreach($rows as $row => $data){ 
	print "ROW: ".$row." and data: ".$data;
if($step==1){	
  foreach ($months as $mon) {
    if(strpos($data, $mon)!==false)
	{   echo "found month :"+ $mon;
	    echo "data :"+ $data; 
	    $meetMonthIndex = strpos($data, $mon);
		$meetData = substr($data, $meetMonthIndex);
		$meetDates = fetchMeetDates($data, $meetMonthIndex);
		var_dump($meetDates);
		$meet1Date =$meetDates[0];
		$meet2Date = $meetDates[1];
		$payable_to = "Southern California Swimming";
	    $payment_instructions = "N/A";
	    $max_per_kid_signup = 4;  
            	$per_event_charge = 4;
		$min_eligible_age = 11;
	    $signup_deadline = $GLOBALS['meet_deadline'];
	    $sql = "insert into meet(meet_name, meet_date1, meet_date2, min_eligible_age, payable_to, per_event_charge, payment_instructions, max_per_kid_signup, signup_deadline)values('".$meetName."', '".$meet1Date."','".$meet2Date."',$min_eligible_age,'".$payable_to ."','".$per_event_charge."', '".$payment_instructions."', '".$max_per_kid_signup."', '".$signup_deadline."')";
	  
	$conn = connectToDB();
	UpdateDB($conn, $sql);   
	$meetIdSql = "select max(meet_id) from meet";   
	$row = fetchFromDB($conn, $meetIdSql); 
	$meetId = $row[0]; 
	$GLOBALS['meetId'] = $meetId; 
	$GLOBALS['conn'] = $conn;  
		$step = $step+1;
		break;
	}
	 //Get First and Second Meet Dates 
  } 
}	
}
$pageNumber = 2;
$secondPage = trim($converter->textFromPdfFile($pdf, $pageNumber));	
//echo $secondPage;
$skipElements = array("Relays", "Time", "Mixed", "Permitting", "OPEN");
$rows = explode("\n", $secondPage);
$i = -1;
$AdditionalInfo1 = "";
$AdditionalInfo2 ="";	
$sessionType = "Morning";	
//Variables to detect Table start and end. 4 Tables are expected in sample pdfs.
// Considering First Table as First two tables in PDF which are at the same level.
//Considering Second Table as second two tables in PDF below the First Table.	
$detectedFirstTableEnd = false;
$detectedSecondTableStart = false;
$detectedSecondTableEnd = false;
$missingEventNumbers = array();	
$detectedOPENFirst = 0;
$detectedOPENSecondStart = 0;	
$detectedOPENSecondEnd = 0;	
$markSecondTableStart = false;
$currentFirstEventNumber = 0;
$currentSecondEventNumber = 0;	
$step = 1;
//Indicates of we have found warm up and meet times	
$warmupAndMeetTimeInd = false;	
foreach($rows as $row => $data)
{   //row has no word in it; skip it
	$shouldProcessRow = true;
	echo "row ".$row." and data is : ".$data."<br>"." and step is ".$step;
 //Check if row contains dates of the meet
if($step==1){	
  foreach ($months as $mon) {
    if(strpos($data, $mon)!==false)
	{
		if(strpos($data, "Date of Meet:")!==false){
			$dateInfoSplit = explode("Date of Meet:", $data);
			$meetName = $dateInfoSplit[1];
		}
		else{
		    $meetName = $data; 	
		}
		echo "Meet Name: ".$meetName."<br>";
		$meetDates = fetchMeetDates($meetName);
		var_dump($meetDates);
		$meet1Date =$meetDates[0];
		$meet2Date = $meetDates[1];
		$payable_to = "Southern California Swimming";
	    $payment_instructions = "N/A";
	    $max_per_kid_signup = 4;  
            	$per_event_charge = 4;
		$min_eligible_age = 11;
	    $signup_deadline = $GLOBALS['meet_deadline'];
	    $sql = "insert into meet(meet_name, meet_date1, meet_date2, min_eligible_age, payable_to, per_event_charge, payment_instructions, max_per_kid_signup, signup_deadline)values('".$meetName."', '".$meet1Date."','".$meet2Date."',$min_eligible_age,'".$payable_to ."','".$per_event_charge."', '".$payment_instructions."', '".$max_per_kid_signup."', '".$signup_deadline."')";
	  
	$conn = connectToDB();
	UpdateDB($conn, $sql);   
	$meetIdSql = "select max(meet_id) from meet";   
	$row = fetchFromDB($conn, $meetIdSql); 
	$meetId = $row[0]; 
	$GLOBALS['meetId'] = $meetId; 
	$GLOBALS['conn'] = $conn;  
		$step = $step+1;
		break;
	}
	 //Get First and Second Meet Dates 
  } 
}
else if(strpos($data, "Warm Up Time")!==false){
	$warmUpAndMeetTimes = $data;
	$warmUpMeetStarttimes = explode("Meet Start Time:", $warmUpAndMeetTimes);
	$warmUpTime1 = explode("Time:", $warmUpMeetStarttimes[0])[1];
	$timeSplit = explode("Warm Up Time:", $warmUpMeetStarttimes[1]);	  
	$meetStartTime1 = $timeSplit[0];
	$warmUpTime2 = $timeSplit[1];  
	$meetStartTime2 = $warmUpMeetStarttimes[2];  
    echo "<b>Warm Up Time of Meet 1 : </b>". $warmUpTime1."<br>";
    echo "<b>Meet Start Time of Meet 1 : </b>". $meetStartTime1."<br>";
    echo "<b>Warm Up Time of Meet 2 : </b>". $warmUpTime2."<br>";
    echo "<b>Meet Start Time of Meet 2 : </b>". $meetStartTime2."<br>";
	
	$GLOBALS['warmUpTime1'] =  $warmUpTime1;
	$GLOBALS['meetStartTime1'] =  $meetStartTime1;
	$GLOBALS['warmUpTime2'] =  $warmUpTime2;
	$GLOBALS['meetStartTime2'] =  $meetStartTime2; 
 }
else if($step==2){ 	
	if(stripos($data, "Girls")!==false && stripos($data, "Age")!==false && stripos($data, "Min")!==false){
		$step++;
	//marks the start of table
	}
}	
else{
	//check if this row contains warm up and meet times		
	  echo "**************************"."<br";
	  echo "row number ".$row." and data : ".$data."<br>";
	  echo "current first ".$currentFirstEventNumber."<br>";
		echo "current second ".$currentSecondEventNumber."<br>";
	if(!$detectedFirstTableEnd){
		  
		  $dataTemp = skipElements($data);
		  $arrTemp = explode(" ",$dataTemp);	
		  if(hasAtleastOneLegitimateWord($arrTemp)){
			  if(checkifEventEntry($data, $currentFirstEventNumber, $currentSecondEventNumber)==false){
			     $detectedFirstTableEnd = true;
		      }
		  }
	   
	   
	}
    else if($detectedSecondTableStart && !$detectedSecondTableEnd){
		echo "current first ".$currentFirstEventNumber;
		echo "current second ".$currentSecondEventNumber;
		if(str_word_count($data)>2){
		 if(checkifEventEntry($data, $currentFirstEventNumber, $currentSecondEventNumber)==false){
			$detectedSecondTableEnd = true;
			echo "INSIDE DETCETED SECOND TABLE END";
	    	if($AdditionalInfo2 == "")
		      $AdditionalInfo2 = $AdditionalInfo2.$data;
			 else
			   $AdditionalInfo2 = $AdditionalInfo2."\n". $data;
		   echo "print Additional Info2";
		   echo $AdditionalInfo2."<br>"; 
	      }
		}
		 }
	else if($detectedFirstTableEnd && !$detectedSecondTableStart){
			if(strpos($data, "Girls")!==false && strpos($data, "Age")!==false && strpos($data, "Min")!==false){
				$markSecondTableStart = true;
			    echo "marked second table start";
		
		}
		 else if(checkifBothEventEntry($data, $currentFirstEventNumber, $currentSecondEventNumber)==true){
			     $detectedSecondTableStart = true;
			     $sessionType = "Afternoon";
		     }
		else{
			if($AdditionalInfo1 == "")
				$AdditionalInfo1 =$AdditionalInfo1.$data;
			 else
				 $AdditionalInfo1 = $AdditionalInfo1. $data;
			echo "print Additional Info1";
		   echo $AdditionalInfo1."<br>";
		}
	}
    else if($detectedSecondTableEnd){
		if(str_word_count($data)>2){
		echo "INSIDE DETECTED SECOND TABLE END";
	    	if($AdditionalInfo2 == "")
		      $AdditionalInfo2 = $AdditionalInfo2.$data;
			 else
			   $AdditionalInfo2 = $AdditionalInfo2."\n". $data;
		   echo "print Additional Info2";
		   echo $AdditionalInfo2."<br>";
		}
		else{
			echo "SKIPPED <2WORDS";
		}
		}
echo "detectedFirstTableEnd ";
echo $detectedFirstTableEnd?'true':'false';
echo "<br>";	  
echo "detectedSecondTableStart ";
echo $detectedSecondTableStart?'true':'false';
echo "<br>";
echo "detectedSecondTableEnd ";
echo $detectedSecondTableEnd?'true':'false';
echo "<br>";	
echo "detectedOPENFirst ".$detectedOPENFirst;
echo "<br>";
echo "detectedOPENSecondStart ".$detectedOPENSecondStart;	
echo "<br>";	  
echo "detectedOPENSecondEnd ".$detectedOPENSecondEnd;	  
echo "<br>";	  
if(!$detectedFirstTableEnd || ($detectedSecondTableStart && !$detectedSecondTableEnd)){
	$switchFirstEventToSecond = false;
	$currentFirstEventNumTemp  = $currentFirstEventNumber;
	//prepare the character array
	$eventRow = $data;
	var_dump($eventRow);
	$actualEventDetails = skipElements($eventRow);
	echo "fffffffffffffff".sizeof($actualEventDetails);
	var_dump($actualEventDetails);
	$eventInfoSplit = explode(" ", $actualEventDetails);

	//Handling case where only partial event information is exracted by PDFBox
	if(ageEligibilityDesc($eventInfoSplit[0])){
		//Partial first Event Details-First Event Details are missing so ignore the frst word
		if(ageEligibilityDesc($eventInfoSplit[1])){
		//Partial Second Event Details -Right table details are also partially extracted
		//we ignore processing this row all together-HeadCoach will have edit option on screen to populate the missing event information
		$shouldProcessRow = false;	
		if($shouldProcessRow==false){
			echo "NOT TO BE PROCESSED";
		}
	}
	else{//Left table data is partially extracted and right table data is fully extracted
			//just remove the first Event Detail and parse the second
			array_shift($eventInfoSplit);
		    echo "ddddddddddddddddddd ".$eventInfoSplit[0];
			
        }
	}
	else{
		//if we all have is time and age eligibility description - skip as row is not extracted properly
		
		if(!hasAtleastOneLegitimateWord($eventInfoSplit)){
			$shouldProcessRow = false;
			if($shouldProcessRow==false){
			echo "NOT TO BE PROCESSED";
		}	
		}
		else{   
		     if(strpos($eventInfoSplit[0], ":")==true){
				 if(ageEligibilityDesc($eventInfoSplit[1])){
					 array_shift($eventInfoSplit);
					 array_shift($eventInfoSplit);
				 }
			 }
		}
		}
	if($shouldProcessRow){
	//echo "eventInfoSize : ".sizeof($eventInfoSplit);
	//Retrieve the event Name indexes of left table
	//FirstEvent may be missing or has event number missing
	$firstEventNameIndexes = findFirstEventName($eventInfoSplit);
	sort($firstEventNameIndexes);
		
	$firstEventName = "";
	//First Event Name
	for($i=0;$i<sizeof($firstEventNameIndexes);$i++){
		$firstEventName = $firstEventName." ". $eventInfoSplit[$firstEventNameIndexes[$i]];
	}
	for($i=0;$i<sizeof($firstEventNameIndexes);$i++){
		echo "<br>". "First Name Index is".$firstEventNameIndexes[$i];
	}
	
	//var_dump($firstEventNameIndexes);
	$secondEventNameIndexes = findSecondEventName($eventInfoSplit, $firstEventNameIndexes);
	sort($secondEventNameIndexes);	
	for($i=0;$i<sizeof($secondEventNameIndexes);$i++){
		echo "<br>". "Second Name Index is".$secondEventNameIndexes[$i];
	}		
	echo "STARTARTARTARTARAT ".	$firstEventNameIndexes[0];
	//Remove OPEN if present before Event Name
	$index1 = removeExtraTextBeforeEventName($eventInfoSplit, $firstEventNameIndexes);
	echo "INDEX 1 : ".$index1."<br>";
	$index2 = removeExtraTextBeforeEventName($eventInfoSplit, $secondEventNameIndexes);
	echo "INDEX 2 : ".$index2."<br>";
	
	
	if($index2==$firstEventNameIndexes[sizeof($firstEventNameIndexes)-1]+1){
		$index2 = "None";
	}	
	if($index1!="None" || $index2!="None")	
	  $eventInfoSplit = makeShift($eventInfoSplit, $index1, $index2);	
	if($index1!="None"){
		echo "size inside ".sizeof($firstEventNameIndexes);
		for($i=0;$i<sizeof($firstEventNameIndexes);$i++){
			$valueIndex = $firstEventNameIndexes[$i];
			echo "&&&&&&&&&&&&&&& ".$valueIndex;
			$newValueIndex = $valueIndex-1;
			echo "&&&&&&&&&&&&&&& ".$newValueIndex;
			$firstEventNameIndexes[$i] = $newValueIndex;
		}
		for($i=0;$i<sizeof($secondEventNameIndexes);$i++){
			$valueIndex = $secondEventNameIndexes[$i];
			echo "&&&&&&&&&&&&&&& ".$valueIndex;
			$newValueIndex = $valueIndex-1;
			echo "&&&&&&&&&&&&&&& ".$newValueIndex;
			$secondEventNameIndexes[$i] = $newValueIndex;
		}
	}
			
	if($index2!="None"){
		for($i=0;$i<sizeof($secondEventNameIndexes);$i++){
			$valueIndex = $secondEventNameIndexes[$i];
			echo "&&&&&&&&&&&&&&& ".$valueIndex;
			$newValueIndex = $valueIndex-1;
			echo "&&&&&&&&&&&&&&& ".$newValueIndex;
			$secondEventNameIndexes[$i] = $newValueIndex;
		}
	}	
    echo "<br> List all indexes before<br>";
	for($i=0;$i<sizeof($firstEventNameIndexes);$i++){
		echo "<br>". "First Name Index is".$firstEventNameIndexes[$i];
	}
	for($i=0;$i<sizeof($secondEventNameIndexes);$i++){
		echo "<br>". "Second Name Index is".$secondEventNameIndexes[$i];
	}	
			
	var_dump($eventInfoSplit);
    //Find Age Eligibility
	$firstEventAgeIndex = $firstEventNameIndexes[sizeof($firstEventNameIndexes)-1]+1;
	$firstEventAge = $eventInfoSplit[$firstEventAgeIndex];
	//First Event - Get Details for Girls
	$prevIndex = $firstEventNameIndexes[0]-1;
	echo "aaaaaaaaaa ".$prevIndex;
	//Default Values
	$firstEventGirlsEligibility = "N";
	$firstEventBoysEligibility = "N";
	$firstEventNumberGirls = "N/A";
	$firstEventNumberBoys = "N/A";	
	$firstEventGirlsMin = "N/A";
	$firstEventBoysMin = "N/A";
	if($prevIndex>=0){
	if(strpos($eventInfoSplit[$prevIndex], ':')!== false)
	{
		$firstEventGirlsMin = $eventInfoSplit[$prevIndex];
	    $prevIndex--;
	}
	if($prevIndex>=0 && $eventInfoSplit[$prevIndex]!="***"){
		$firstEventNumberGirls = $eventInfoSplit[$prevIndex];
		if($firstEventNumberGirls!=$currentFirstEventNumber+1)
			array_push($missingEventNumbers, 2);
		$currentFirstEventNumber = $firstEventNumberGirls;
		echo "<br> currentFirstEventNumberrrrrrrrrrrrrrrrrrrr ".$currentFirstEventNumber."<br>";
		$firstEventGirlsEligibility = "Y";
	}
	}
	//First Event - Get Details for Boys
	$nextIndex = $firstEventAgeIndex + 1;
	if(strpos($eventInfoSplit[$nextIndex], ':')!== false)
	{
		$firstEventBoysMin = $eventInfoSplit[$nextIndex];
	    $nextIndex++;
	}
	else
	{
		$firstEventBoysMin = "N/A";
	}
	if(sizeof($secondEventNameIndexes)==0){
		//Lets check if Event Details we found belongs to the left table or right
		if($firstEventGirlsEligibility=="Y"){
			  if(foundNextEventNumber($firstEventNumberGirls, $currentSecondEventNumber)){
				//This means left Table ended and this data belongs to the right table
				$currentSecondEventNumber = $firstEventNumberGirls;
				$switchFirstEventToSecond = true;
				  
			}
		}
		if($nextIndex<sizeof($eventInfoSplit))
		{
		if($eventInfoSplit[$nextIndex]!="***"){
		  $firstEventNumberBoys = $eventInfoSplit[$nextIndex];
		  $firstEventBoysEligibility = "Y";
		  if(foundNextEventNumber($firstEventNumberBoys, $currentSecondEventNumber)){
			  $currentSecondEventNumber = $firstEventNumberBoys;
			  $switchFirstEventToSecond = true; 
		  }
		  else{
			  $currentFirstEventNumber = $firstEventNumberBoys;
		  }	
	     if($switchFirstEventToSecond==true){
			 $currentSecondEventNumber = $firstEventNumberBoys;
		 } 
	 }
	}
	$secondEventGirlsEligibility = "N";
	$secondEventBoysEligibility = "N";
	$secondEventNumberGirls = "N/A";
	$secondEventName = "N";
	$secondEventAge = "N";		
	$secondEventNumberBoys = "N/A";	
	$secondEventGirlsMin = "N/A";
	$secondEventBoysMin = "N/A";		
	}
	else{
	$secondEventGirlsEligibility = "N";
	$secondEventBoysEligibility = "N";
	$secondEventNumberGirls = "N/A";
	$secondEventNumberBoys = "N/A";	
	$secondEventGirlsMin = "N/A";
	$secondEventBoysMin = "N/A";	
	
	//var_dump($secondEventNameIndexes);
	$secondEventAgeIndex = $secondEventNameIndexes[sizeof($secondEventNameIndexes)-1]+1;	
	$secondEventPrevIndex = $secondEventNameIndexes[0]-1;
	echo "secondEventPrevIndex :".$secondEventPrevIndex;
	echo "secondEventPrevIndexValue :".$eventInfoSplit[$secondEventPrevIndex];	
	$secondEventName = "";
	//Second Event Name
	for($i=0;$i<sizeof($secondEventNameIndexes);$i++){
		$secondEventName = $secondEventName." ". $eventInfoSplit[$secondEventNameIndexes[$i]];
	}
		
	$secondEventAge = $eventInfoSplit[$secondEventAgeIndex];
	if(strpos($eventInfoSplit[$secondEventPrevIndex], ':')!== false)
	{
		$secondEventGirlsMin = $eventInfoSplit[$secondEventPrevIndex];
	    $secondEventPrevIndex--;
	}
	else
	{
		$secondEventGirlsMin = "N/A";
	}
	echo "CurrentFirstEventNumber ".$currentFirstEventNumber;
	echo "checking ".$eventInfoSplit[$secondEventPrevIndex];
		
	if(foundNextEventNumber($eventInfoSplit[$secondEventPrevIndex], $currentFirstEventNumber) || $eventInfoSplit[$secondEventPrevIndex] == $currentFirstEventNumber){
		//Found Event Number of First Event for Boys
		$firstEventNumberBoys = $eventInfoSplit[$secondEventPrevIndex];
		$currentFirstEventNumber = $firstEventNumberBoys;
		$firstEventBoysEligibility = "Y";
		//This also means second event is not eligible for girls
	}
	else if($secondEventPrevIndex == $firstEventAgeIndex){
				//This means second event is not eligible for girls
	}
	else if($eventInfoSplit[$secondEventPrevIndex] == "***"){
			//Second Event is not eligible for Girls
			if($secondEventPrevIndex-1 == $firstEventAgeIndex)
			{
				//First Event is not eligible for Boys
			}
			else if($eventInfoSplit[$secondEventPrevIndex-1] == "***")
			{
				//First Event is not eligible for Boys
			}
			else{
				//Found Event Number of First Event for Boys
				$firstEventNumberBoys = $eventInfoSplit[$secondEventPrevIndex-1];
				$firstEventBoysEligibility = "Y";
				$currentFirstEventNumber = $firstEventNumberBoys;
			}
	}
	else{
		//Found Second Event Number for Girls
		$secondEventNumberGirls = $eventInfoSplit[$secondEventPrevIndex];
		$secondEventGirlsEligibility = "Y";
		$currentSecondEventNumber = $secondEventNumberGirls;
		if($secondEventPrevIndex-1 == $firstEventAgeIndex)
		{
				//First Event is not eligible for Boys
		}
		else if($eventInfoSplit[$secondEventPrevIndex-1] == "***")
		{
				//First Event is not eligible for Boys
		}
		else
		{
			//Found Event Number of First Event for Boys
			$firstEventNumberBoys = $eventInfoSplit[$secondEventPrevIndex-1];
			$firstEventBoysEligibility = "Y";
			$currentFirstEventNumber = $firstEventNumberBoys;
		}
	}
	$nextIndex = $secondEventAgeIndex+1;
	//echo "nexttttttttttt ".$nextIndex;	
	if($nextIndex < sizeof($eventInfoSplit)){
		//See if Boys Minimum Time information is present
	   if(strpos($eventInfoSplit[$nextIndex], ':')!== false)
	   {
		  $secondEventBoysMin = $eventInfoSplit[$nextIndex]; 
	      $nextIndex++;
	   }
	   if($eventInfoSplit[$nextIndex]!="***"){
		  $secondEventNumberBoys = $eventInfoSplit[$nextIndex];
		  $secondEventBoysEligibility = "Y";
		   $currentSecondEventNumber = $secondEventNumberBoys; 
	   }	
	}		
		
   }
if($switchFirstEventToSecond==true){
echo "Switching";
$secondEventNumberGirls = $firstEventNumberGirls;
$secondEventGirlsEligibility = $firstEventGirlsEligibility;
$secondEventGirlsMin = $firstEventGirlsMin;
$secondEventName = $firstEventName;
$secondEventAge = $firstEventAge;
$secondEventBoysMin = $firstEventBoysMin;
$secondEventBoysEligibility  = $firstEventBoysEligibility;
$secondEventNumberBoys = $firstEventNumberBoys;
$firstEventGirlsEligibility = "N";
$firstEventBoysEligibility ="N";
$currentFirstEventNumber = $currentFirstEventNumTemp;
echo "returned current First Event Number back to : ".$currentFirstEventNumber."<br>";	
}
echo "SwitchFirstEventToSecond ".$switchFirstEventToSecond."<br>";		
echo "FirstEventNumberGirls ".$firstEventNumberGirls."<br>";
echo "firstEventGirlsEligibility ".$firstEventGirlsEligibility."<br>";
echo "firstEventGirlsMin ".$firstEventGirlsMin."<br>";
echo "firstEventName ".$firstEventName."<br>";
echo "firstEventAge ".$firstEventAge."<br>";
echo "firstEventBoysMin ".$firstEventBoysMin."<br>";
echo "firstEventBoysEligibility ".$firstEventBoysEligibility."<br>";
echo "FirstEventNumberBoys ".$firstEventNumberBoys."<br>";

echo "SecondEventNumberGirls ".$secondEventNumberGirls."<br>";
echo "SecondEventGirlsEligibility ".$secondEventGirlsEligibility."<br>";
echo "SecondEventGirlsMin ".$secondEventGirlsMin."<br>";
echo "SecondEventName ".$secondEventName."<br>";
echo "SecondEventAge ".$secondEventAge."<br>";
echo "SecondEventBoysMin ".$secondEventBoysMin."<br>";
echo "SecondEventBoysEligibility ".$secondEventBoysEligibility."<br>";
echo "SecondEventNumberBoys ".$secondEventNumberBoys."<br>";	
echo "detectedFirstTableEnd ";
echo $detectedFirstTableEnd?'true':'false';
echo "<br>";	  
echo "detectedSecondTableStart ";
echo $detectedSecondTableStart?'true':'false';
echo "<br>";
echo "detectedSecondTableEnd ";
echo $detectedSecondTableEnd?'true':'false';
echo "<br>";	
echo "detectedOPENFirst ".$detectedOPENFirst;
echo "<br>";
echo "detectedOPENSecondStart ".$detectedOPENSecondStart;	
echo "<br>";	  
echo "detectedOPENSecondEnd ".$detectedOPENSecondEnd;	  
echo "<br>";	  
printEvent($meet1Date, $firstEventNumberGirls, $firstEventGirlsEligibility, $firstEventGirlsMin, $firstEventName, $firstEventAge, $firstEventBoysMin, $firstEventBoysEligibility, $firstEventNumberBoys, $sessionType);	

printEvent($meet2Date, $secondEventNumberGirls, $secondEventGirlsEligibility, $secondEventGirlsMin, $secondEventName, $secondEventAge, $secondEventBoysMin, $secondEventBoysEligibility, $secondEventNumberBoys, $sessionType);	
	}
}
		
 }
if($markSecondTableStart){
	$detectedSecondTableStart = true;	
    $sessionType = "Afternoon";
}
}
$conn = $GLOBALS['conn'];
$meetId = $GLOBALS['meetId'];	
$sql = "update meet set AdditionalInfo1='".$AdditionalInfo1."', AdditionalInfo2='".$AdditionalInfo2."' where meet_id=$meetId";	
UpdateDB($conn, $sql);
}

function printEvent($meetDate, $girlsEventNumber, $girlsEligibility, $girlsMin, $eventName, $eventAge, $boysMin, $boysEligibility, $boysEventNumber, $sessionType){
	
	$meetId = $GLOBALS['meetId'];
	$conn = $GLOBALS['conn'];
	if(($girlsEligibility == $boysEligibility) && $boysEligibility == "Y" && ($girlsEventNumber==$boysEventNumber)){
		$sql = "insert into event values($girlsEventNumber, $meetId, '".$eventName."', 'Mixed','".$eventAge."',  '".$meetDate."', '".$girlsMin."', '".$sessionType."','N/A')";
		UpdateDB($conn, $sql);
		echo $sql;
	        echo "<tr>";
		echo "<td> Girls and Boys</td>";
		echo "<td>".$girlsEventNumber."</td>";
		echo "<td>".$girlsMin."</td>";
		echo "<td>".$eventName."</td>";
		echo "<td>".$eventAge."</td>";
		echo "<td>".$meetDate."</td>";
		echo "</br>";
	
	}
	else{
	 if($girlsEligibility == "Y")
	{
		$sql = "insert into event values($girlsEventNumber, $meetId, '".$eventName."', 'Girls','".$eventAge."', '".$meetDate."', '".$girlsMin."','".$sessionType."','N/A')";
		UpdateDB($conn, $sql);
		echo $sql;
		echo "<tr>";
		echo "<td> Girls </td>";
		echo "<td>".$girlsEventNumber."</td>";
		echo "<td>".$girlsMin."</td>";
		echo "<td>".$eventName."</td>";
		echo "<td>".$eventAge."</td>";
		echo "<td>".$meetDate."</td>";
		echo "</br>";
	}
	if($boysEligibility == "Y")
	{
        $sql = "insert into event values($boysEventNumber, '".$meetId."', '".$eventName."', 'Boys','".$eventAge."', '".$meetDate."', '".$boysMin."', '".$sessionType."','N/A')";
		UpdateDB($conn, $sql); 
		echo $sql;
		echo "<tr>";
		echo "<td> Boys </td>";
		echo "<td>".$boysEventNumber."</td>";
		echo "<td>".$boysMin."</td>";
		echo "<td>".$eventName."</td>";
		echo "<td>".$eventAge."</td>";
		echo "<td>".$meetDate."</td>";
		echo "</br>";
	}
	}
}
function removeExtraTextBeforeEventName($dataArr, $eventNameIndexes){
 echo "inside removeExtraTextBeforeEventName";
  $index1 = "None";	
  if(sizeof($eventNameIndexes)>0){
	$eventStartIndex = $eventNameIndexes[0];
	if($eventStartIndex-1>=0){
	echo "checking to be removes index ".($eventStartIndex-1)." which has value ".$dataArr[$eventStartIndex-1];	
    if($dataArr[$eventStartIndex-1]=="OPEN" || $dataArr[$eventStartIndex-1]=="***")
	   return $eventStartIndex-1;
   }
  }
  return $index1;
}
function makeShift($dataArr, $index1, $index2){
   $newEventArr = array();
   if($index1=="None"){
	 for($i=0;$i<$index2;$i++){
	   array_push($newEventArr, $dataArr[$i]);
     }
	 for($i=$index2+1;$i<sizeof($dataArr);$i++){
	   array_push($newEventArr, $dataArr[$i]);
     }  
   }
   else{
	    for($i=0;$i<$index1;$i++){
	      array_push($newEventArr, $dataArr[$i]);
        }
	    if($index2=="None"){
		 for($i=$index1+1;$i<sizeof($dataArr);$i++){
	      array_push($newEventArr, $dataArr[$i]);
		}
		}
		else{
			for($i=$index1+1;$i<$index2;$i++){
	           array_push($newEventArr, $dataArr[$i]);
			}
		    for($i=$index2+1;$i<sizeof($dataArr);$i++){
	          array_push($newEventArr, $dataArr[$i]);		
             }	
            }
   }
    var_dump($newEventArr);
	return $newEventArr;
 
}
function hasAtleastOneLegitimateWord($eventInfoSplit){
	$pattern = '/[a-zA-Z]/';
	for($i=0;$i<sizeof($eventInfoSplit);$i++){
	   $subject = $eventInfoSplit[$i];
      if (preg_match($pattern, $subject) && !ageEligibilityDesc($subject)){
		return true;
	  }
	}
	return false;	
    }
function checkifEventEntry($data, $currentFirstEventNumber, $currentSecondEventNumber){
	echo "inside fxn checkifevententry ".$data;
	for($i=1;$i<=5;$i++){
		$subject = $currentFirstEventNumber+$i;
		echo "check ".$subject;
		if(strpos($data, (string)$subject)==true){
			echo "yessssssssssssssssssssss";
			return true;
		}
		}
	
	for($i=1;$i<=5;$i++){
		$subject = $currentSecondEventNumber+$i;
		if(strpos($data, (string)$subject)==true){
			return true;
		}
	}
	return false;
}
function checkifBothEventEntry($data, $currentFirstEventNumber, $currentSecondEventNumber){
	echo "inside fxn checkifBothevententry ".$data;
	$counter = 0;
	for($i=1;$i<=5;$i++){
		$subject = $currentFirstEventNumber+$i;
		echo "check ".$subject;
		if(strpos($data, (string)$subject)==true){
			echo "yessssssssssssssssssssss".$subject;
			$counter++;
			break;
		}
	}
	
	for($i=1;$i<=5;$i++){
		$subject = $currentSecondEventNumber+$i;
		if(strpos($data, (string)$subject)==true){
			$counter++;
			break;
		}
	}
	if($counter==2)
		return true;
	else
		return false;
}
function foundNextEventNumber($first, $second){
	for($i=1;$i<=5;$i++){
		if($first==$second+$i){
			return true;
		}
	}
	return false;
}
function findSecondEventName($eventInfoSplit, $firstEventIndexes){
	$subject = "abcdef";
$pattern = '/[a-zA-Z]/';
$indexes = array();	
$i=0;
$found = 0;	
  for($i=0;$i<sizeof($eventInfoSplit);$i++){
	$subject = $eventInfoSplit[$i];
	//echo "i is ". $i. "value is: ".$subject."<br>";
    if (preg_match($pattern, $subject) && !ageEligibilityDesc($subject)){
      echo "matched at index ".$i. " value is: ". $eventInfoSplit[$i]; 
	 if(!in_array($i, $firstEventIndexes)){	
      echo "<br>**second event matched at index".$i. " value is: ". $eventInfoSplit[$i];
	  array_push($indexes, $i);
	  array_push($indexes, $i-1);
	  $found = 1;	 
	  break;	
	 }
  }
}
    echo "found ".$found;
	if($found == 0){
		$empty_array = array();
		return $empty_array;
	}
	//echo("i is ". $i);
	$j = ++$i; 
	echo ("j is ". $j);
  while(!ageEligibilityDesc($eventInfoSplit[$j]) && !checkSkipElements($eventInfoSplit[$j]))   {
		array_push($indexes, $j);
		$j++;	
	}
	return $indexes;
}
function findFirstEventName($eventInfoSplit){
	$subject = "abcdef";
$pattern = '/[a-zA-Z]/';
$indexes = array();	
$i=0;
  for($i=0;$i<sizeof($eventInfoSplit);$i++){
	$subject = $eventInfoSplit[$i];
	//echo "i is ". $i. "value is: ".$subject."<br>";
    if (preg_match($pattern, $subject) && !ageEligibilityDesc($subject)){
     echo "<br> **matched at index ".$i. " value is: ". $eventInfoSplit[$i]; 
   	  array_push($indexes, $i);
	  array_push($indexes, $i-1);	
	  break;	
  }
}
	echo("<br> i is ". $i);
	$j = ++$i; 
	echo ("<br> j is ". $j);
  while(!ageEligibilityDesc($eventInfoSplit[$j]) && !checkSkipElements($eventInfoSplit[$j]))   {
		array_push($indexes, $j);
		$j++;	
	}
	return $indexes;
}
function ageEligibilityDesc($eventString){
    if(strpos($eventString, '-')== true || $eventString=="OPEN"|| strpos($eventString, '&')== true){
		echo "ohooooooooooo ".$eventString;
		return true;
	}
}	
function skipElements($a){
	$skipElements = array("RELAYS", "TIME", "MIXED", "PERMITTING", "DECK", "ENTERED");
	$rowData = $a;
	for($i=0;$i<sizeof($skipElements);$i++){
		$rowData = str_ireplace($skipElements[$i],"",$rowData);
        		
	}
	//remove double whitespaces between words
	$stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $rowData);
	echo "line ". trim($stripped);
	return trim($stripped);
}
function checkSkipElements($a){
	$b = strtoupper($a);
	$b = trim($a);
	echo "Checking for skip elements ".$a;
	$skipElements = array("RELAYS", "TIME", "MIXED", "PERMITTING", "OPEN", "DECK", "ENTERED");
	if(in_array($b, $skipElements) && $a!="Open"){
		return true;
	}
	
}
function fetchMeetDates($data, $startIndex){
	echo $data;
	echo "aaaaa ".$startIndex;
	$monthdates = substr($data, $startIndex);
	$stripped = preg_replace(array('/\s{2,}/', '/[ \t\n]/'), ' ', $monthdates);
	$arr = explode(' ',trim($stripped)); 
	//var_dump($arr);
	$meetMonth = $arr[0];
	$meetMonthEndIndex = strlen($meetMonth);
	echo "bbbbbbbb ".$meetMonthEndIndex;
	$meetYear="";
	echo "meetMonth ".$meetMonth;
	echo "Strippd: ".$stripped;
	$meetDates = substr($stripped, $meetMonthEndIndex);
	echo "meetdates :".$meetDates;
	echo "doen";
	//remove spaces
	str_replace(" ","",$meetDates);
	echo "Afer eliminating spaces".$meetDates;  - we shud not actually to be able to distinguis when year ends or may be take length 4
	if(strpos($meetDates, "-")==true){
		$meetDatesArr = explode("-", $meetDates);
		$meet1Date = $meetDatesArr[0];
		if(strpos($meetDatesArr[1],",")==true){
	      $temp = explode(',', $meetDatesArr[1]);
	      $meet2Date = $temp[0];
		  $meetYearTemp = split($temp[1], " ");
		  $meetYear = $meetYearTemp[0];
	    }
		else{
			$meet2Date = $meetDatesArr[1];
		}
	}
	if(strpos($meetDates, "&")==true){
		$meetDatesArr = explode("&", $meetDates);
		$meet1Date = $meetDatesArr[0];
		echo "meet 1 date:".$meet1Date."<br>";
		echo "next part: ".$meetDatesArr[1];
		if(strpos($meetDatesArr[1],",")==true){
		  echo "HERE"."<br>";
	      $temp = explode(',', $meetDatesArr[1]);
		  var_dump($temp);	
	      $meet2Date = $temp[0];
		  echo 'temp '+$meet2Date;
		  $meetYearTemp = split(trim($temp[1]), " ");
		  $meetYear = $meetYearTemp[0];	
	    }
		else{
			$meet2Date = $meetDatesArr[1];
		}
	}

	$meet1FullDate = $meetMonth." ".$meet1Date.", ".$meetYear;
	$meet2FullDate = $meetMonth." ".$meet2Date.", ".$meetYear;
	echo "meet1FullDate: ".$meet1FullDate;
	echo "meet2FullDate: ".$meet2FullDate;
	$meetDatesArr2 = array();
	array_push($meetDatesArr2, $meet1FullDate);
	array_push($meetDatesArr2, $meet2FullDate);
	return $meetDatesArr2;
}


?>