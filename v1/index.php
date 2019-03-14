<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 6/27/2016
 * Time: 3:06 PM
 */

ini_set('display_errors',1);
require_once '../includes/Db_Function.php';
require_once '../libs/Slim/Slim.php';

//\Slim\Slim::registerAutoloader();

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

function echoResponse($status_code,$response){

    $app = \Slim\Slim::getInstance();

    $app->status($status_code);

    $app->contentType('application/json');
    echo json_encode($response);
}


$app->post('/Login', function() use($app){

    $response = array("error" => FALSE);

    $username = $app->request->post('username');
    $password = $app->request->post('password');

    $user = loginUser($username,$password);

    if($user != false){

        $response["error"] = FALSE;
        $response["message"]="User authentication success.";

        $response["euid"] = $user["euid"];
        $response["username"] = $user["username"];
        $response["password"] = $user["password"];

        $response["firstname"] = $user["firstname"];
        $response["lastname"] = $user["lastname"];
        $response["mi"] = $user["mi"];
        $response["address"] = $user["address"];
        $response["contactno"] = $user["contactno"];
        $response["email"] = $user["email"];
        $response["status"] = $user["status"];
        $response["bdate"] = $user["bdate"];


        echo json_encode($response);

    }else{
        $response["error"] = TRUE;
        $response["message"]="User was unable to authenticate! Because username or password not found. Please try again.";
        echo json_encode($response);
    }
});



$app->get('/getProcessByUserId/:id', function($userId){


    $response = array();
    $result = getProcessByUserId($userId);
    $response["error"] = FALSE;
    $response["users"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["euprocessid"] = $task["euprocessid"];
        $tmp["processname"] = $task["processname"];
        $tmp["schedtype"] = $task["schedtype"];
        $tmp["startdate"] = $task["startdate"];
        $tmp["enddate"] = $task["enddate"];
        $tmp["datefinished"] = $task["datefinished"];
        $tmp["euid"] = $task["euid"];
        $tmp["recurrence"] = $task["recurrence"];
        $tmp["numrec"] = $task["numrec"];
        $tmp["datecreated"] = $task["datecreated"];
        $tmp["datemodified"] = $task["datemodified"];
        $tmp["agencycopiedfrom"] = $task["agencycopiedfrom"];
        $tmp["status"] = $task["status"];
        array_push($response["users"],$tmp);
    }

    echoResponse(200,$response);

});


$app->get('/getRequirements/:id', function($userId){


    $response = array();
    $result = getRequirementsByUserId($userId);
    $response["error"] = FALSE;
    $response["requirements"] = array();

    foreach($result as $task){

        $tmp = array();


        $tmp["reqid"] = $task ["reqid"];
        $tmp["reqname"] = $task ["reqname"];
        $tmp["copy"] = $task ["copy"];
        $tmp["notes"] = $task ["notes"];
        $tmp["reqStatus"] =$task["reqStatus"];
        $tmp["processid"] = $task["processid"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["mobile_requirementsid"] = $task["mobile_requirementsid"];
            
        array_push($response["requirements"],$tmp);
    }

    echoResponse(200,$response);

});




$app->get('/getStepsByProcessId/:id', function($userId){


    $response = array();
    $result = getStepsByUserId($userId);
    $response["error"] = FALSE;
    $response["steps"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["stepid"] = $task["stepid"];
        $tmp["stepdesc"] = $task["stepdesc"];
        $tmp["stepseqno"] = $task["stepseqno"];
        $tmp["stepstatus"] = $task["stepstatus"];
        $tmp["parentstepid"] = $task["parentstepid"];
        $tmp["processid"] = $task["processid"];
        $tmp["user_id"]= $task["user_id"];
        $tmp["mobile_created_id"] = $task["mobile_created_id"];
        array_push($response["steps"],$tmp);
    }

    echoResponse(200,$response);

});

/**
 * get stepcopre
 */
$app->get('/getStepCopreByUserId/:id', function ($userId){

    $response = array();
    $result = getStepCopre($userId);
    $response["error"] = FALSE;
    $response["stepcopre"] = array();

    foreach ($result as $copre){

        $tmp = array();

        $tmp["copreid"] = $copre["copreid"];
        $tmp["stepid"] = $copre["stepid"];
        $tmp["prestepid"] = $copre["prestepid"];
        $tmp["user_id"] = $copre["user_id"];
        $tmp["agencyprocessid"] = $copre["agencyprocessid"];
        $tmp["data_id"] = $copre["data_id"];

        array_push($response["stepcopre"],$tmp);

    }
    echoResponse(200,$response);
});


/**
 * get step required
 */

$app->get('/getStepRequired/:id',function ($userId){

    $response = array();
    $result = getStepRequired($userId);
    $response["error"] = FALSE;
    $response["step_required"] = array();

    foreach ($result as $required){

        $tmp = array();
        
        $tmp["steprequiredid"] = $required["steprequiredid"];
        $tmp["stepid"] = $required["stepid"];
        $tmp["reqid"] = $required["reqid"];
        $tmp["user_id"] = $required["user_id"];
        $tmp["agencyprocessid"] = $required["agencyprocessid"];
        $tmp["data_id"] = $required["data_id"];

        array_push($response["step_required"],$tmp);
    }

    echoResponse(200,$response);
});


//Add process
$app->post('/AddProcess',  function() use ($app) {

    $response = array("error" => FALSE);

    

     $euprocessid = $app->request->post('euprocessid');
     $pname = $app->request->post('pname');
     $schedType = $app->request->post('schedType');
     $start_date = $app->request->post('start_date');
     $date_end = $app->request->post('date_end');
     $dateFinished = $app->request->post('dateFinished');
     $euid = $app->request->post('euid');
     $recurrence = $app->request->post('recurrence');
     $numrec = $app->request->post('numrec');
     $date_create = $app->request->post('date_create');
     $date_modified = $app->request->post('date_modified');
     $agency = $app->request->post('agency');
     $status = $app->request->post('status');


     
     if(CheckUserStatus($euid))
     { 

        if(CheckTheProcessId($euprocessid,$euid))
        {

                 UpdateSyncData($euprocessid,$pname,$schedType,$start_date,$date_end,$dateFinished,
                  $euid,$recurrence,$numrec,$agency,$status);

                    $response["error"] = FALSE;
                    $response["message"] = "Process updated.";
                    echo json_encode($response);

        }// end of CheckTheProcessId
        else 
        {
             $add =  AddProcess($euprocessid,$pname,$schedType,$start_date,$date_end,$dateFinished,
             $euid,$recurrence,$numrec,$date_create,$date_modified,$agency,$status);

                $response["error"] = FALSE;
                $response["message"]="New Process added.";
                echo json_encode($response);
             
        }


    }
     else
     {
        $response["error"] = TRUE;
        $response["message"] = "Sorry! Your account is not Active.";
        echo json_encode($response);
     }
});

//Add Requirements
$app->post('/AddRequirements', function() use ($app){

         $response = array("error" => FALSE);

        $reqname = $app->request->post('reqname');
        $copy    = $app->request->post('copy');
        $notes   = $app->request->post('notes');
        $reqStatus = $app->request->post('reqStatus');
        $processid = $app->request->post('processid');
        $user_id = $app->request->post('user_id');
        $mobile_requirementsid = $app->request->post('mobile_requirementsid');
        $reqid = $app->request->post('reqid');

        if(CheckUserStatus($user_id))
        {


                if (CheckReqIdFromWeb($reqid,$user_id)) {

                    UpdateRequirementsFromWeb($reqname,$copy,$notes,$reqStatus,$reqid,$user_id);
                    $response["error"] = FALSE;
                    $response["message"] = "Process updated.";
                    echo json_encode($response);
                }

                else if (CheckReqIdFromMobile($mobile_requirementsid,$user_id))
                {
                    UpdateRequirementsFromMobile($reqname,$copy,$notes,$reqStatus,$mobile_requirementsid,$user_id);
                    $response["error"] = FALSE;
                    $response["message"] = "Process updated.";
                    echo json_encode($response);
                }
                else
                {
                    addNewRequirements($reqname,$copy,$notes,$reqStatus,$processid,$user_id,$mobile_requirementsid);
                    $response["error"] = TRUE;
                    $response["message"] = "Process sync completed.";
                    echo json_encode($response);
                }



        }else{

            $response["error"] = FALSE;
            $response["message"] = "Sorry! Your account is not Active.";
            echo json_encode($response);

        }

});

$app->post('/SyncSteps', function() use ($app){
    
    $response = array("error" => FALSE);
    
    $stepdesc = $app->request->post('stepdesc');
    $stepseqno = $app->request->post('stepseqno');
    $stepstatus = $app->request->post('stepstatus');
    $parentstepid  = $app->request->post('parentstepid'); 
    $processid  = $app->request->post('processid');
    $user_id = $app->request->post('user_id');
    $mobile_created_id = $app->request->post('mobile_created_id');
    $stepid = $app->request->post('stepid');

    if(CheckUserStatus($user_id))
    {
        if(checkTheIdOfStepsPrimaryKey($stepid,$user_id)){

            UpdateStepsFromWeb($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id, $stepid);
            $response["error"] = FALSE;
            $response["message"] = "steps updated";
            echo json_encode($response);

        }else if (checkTheMobileIdOfSteps($mobile_created_id,$user_id)) {

            UpdateStepsFromMobile($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id,$mobile_created_id);
            $response["error"] = FALSE;
            $response["message"] = "steps updated";
            echo json_encode($response);

        }  else {
            SyncSteps($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id, $mobile_created_id);
            $response["error"] = FALSE;
            $response["message"] = "steps sunc completed";
            echo json_encode($response);
        }
    }else{

        $response["error"] = FALSE;
        $response["message"] = "Sorry! Your account is not Active.";
        echo json_encode($response);
    }

    

});

$app->post('/Add_Step_Copre', function() use($app){

    $response = array("error" => FALSE);
    $copreid = $app->request->post('copreid');
    $stepId = $app->request->post('stepid');
    $prestepid = $app->request->post('prestepid');
    $userId = $app->request->post('user_id');
    $agencyprocessid = $app->request->post('agencyprocessid');
    $data_id = $app->request->post('data_id');


    if(CheckUserStatus($userId)) {

       if(checkTheStepCopre($data_id)){

       }else if(checkTheStepCopreMobileDataId($data_id)){

       }else{
           AddStepCopre($stepId,$prestepid,$userId,$agencyprocessid,$data_id);
       }

    }else{
        $response["error"] = FALSE;
        $response["message"] = "Sorry! Your account is not Active.";
        echo json_encode($response);
    }
});

$app->post('/Add_Step_Required', function() use($app){


    $response = array("error" => FALSE);

    $steprequiredid = $app->request->post('steprequiredid');
    $stepid  = $app->request->post('stepid');
    $reqid = $app->request->post('reqid');
    $userId = $app->request->post('user_id');
    $agencyprocessid = $app->request->post('agencyprocessid');
    $data_id = $app->request->post('data_id');


    if(CheckUserStatus($userId)) {

        if(checkTheStepRequiredId($steprequiredid)){

        }elseif(checkTheStepRequiredDataId($data_id)){

        }else{
            AddStepRequired($stepid,$reqid,$userId,$agencyprocessid,$data_id);
        }

    }else{
        $response["error"] = FALSE;
        $response["message"] = "Sorry! Your account is not Active.";
        echo json_encode($response);
    }
});



/**
 * get all agencies
 */
$app->get('/display_all_agency', function () use ($app){


    $response = array();
    $result = DisplayAllAgency();
    $response["error"] = FALSE;
    $response["agencies"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["agencyid"] = $task["agencyid"];
        $tmp["agencyname"] = $task["agencyname"];
        $tmp["branch"] = $task["branch"];
        $tmp["address"] = $task["address"];
        $tmp["officeno"] = $task["officeno"];
        $tmp["contactperson"] = $task["contactperson"];
        $tmp["contactpersonno"] = $task["contactpersonno"];
        $tmp["contactemail"] = $task["contactemail"];
        $tmp["username"] = $task["username"];
        $tmp["password"] = $task["password"];
        $tmp["logo"] = 'http://192.168.8.100/PhpApiForAndroid/endinmind/agency/agencydashboard/files/'.$task["logo"];
        $tmp["about"]= $task["about"];
        $tmp["status"] = $task["status"];
        $tmp["lastdate_login"] = $task["lastdate_login"];


        array_push($response["agencies"],$tmp);
    }

    echoResponse(200,$response);

});

$app->get('/all_process_in_agency/:id', function ($agencyId){


    $response = array();
    $result = ViewAgencyProcess($agencyId);
    $response["error"] = FALSE;
    $response["agency"] = array();

     foreach($result as $task){

        $tmp = array();

        $tmp["aprocessid"] = $task["aprocessid"];
        $tmp["agencyid"] = $task["agencyid"];
         $tmp["logo"] = 'http://192.168.8.100/PhpApiForAndroid/endinmind/agency/agencydashboard/files/'.$task["logo"];
        $tmp["agencyname"] = $task["agencyname"];
        $tmp["processname"] = $task["processname"];
        $tmp["recurrence"] = $task["recurrence"];
        $tmp["numrec"] = $task["numrec"];
        $tmp["datecreated"] = $task["datecreated"];
        $tmp["datemodified"] = $task["datemodified"];
        $tmp["status"] = $task["status"];
        
       
        array_push($response["agency"],$tmp);
    }

    echoResponse(200,$response);



});

$app->get('/agency_process_details/:id', function ($aprocessid){

    $response = array();
    $result = AgencyProcessDetails($aprocessid);
    $response["error"] = FALSE;
    $response["agency"] = array();

     foreach($result as $task){

        $tmp = array();

        $tmp["aprocessid"] = $task["aprocessid"];
        $tmp["agencyid"] = $task["agencyid"];
        $tmp["processname"] = $task["processname"];
        $tmp["recurrence"] = $task["recurrence"];
        $tmp["numrec"] = $task["numrec"];
        $tmp["datecreated"] = $task["datecreated"];
        $tmp["datemodified"] = $task["datemodified"];
        $tmp["status"] = $task["status"];
        
       
        array_push($response["agency"],$tmp);
    }

    echoResponse(200,$response);

});

$app->post('/download_agency_process', function() use($app){

    $response = array("error" => FALSE);


    $aprocessid = $app->request->post('aprocessid');
    $processname = $app->request->post('processname');
    $recurrence = $app->request->post('recurrence');
    $numrec = $app->request->post('numrec');
    $euid = $app->request->post('euid');
    $schedtype =$app->request->post('schedtype');
    $startdate = $app->request->post('startdate');
    $enddate = $app->request->post('enddate');
    $datefinished =$app->request->post('datefinished');
    $datesubscribed = $app->request->post('datesubscribed');
    $agencyname = $app->request->post('agencyname');
    $status =$app->request->post('status');


    if(CheckAgencyId($aprocessid,$euid)){

        $response["error"] = TRUE;
        $response["message"] = "Sorry! you cannot download this process, because you already downloaded this agency process.";
        echo json_encode($response);

    }else{

        $result  = DownloadAgencyProcess($aprocessid,$processname,$recurrence,$numrec,$euid,$schedtype,
            $startdate,$enddate,$datefinished,$datesubscribed,$agencyname,$status);

        if($result != TRUE){

            $response["error"] = FALSE;
            $response["message"] = "success";
            echo json_encode($response);

        }else{
            $response["error"] = TRUE;
            $response["message"] = "Failed";
            echo json_encode($response);
        }
    }
});


/**
 * get the downloaded the subscribed process and store it in database
 */
$app->get('/get_and_download_process_subscribed/:id', function ($euid){

    $response = array();
    $response["error"] =FALSE;
    $result = get_and_download_subscribed_process($euid);
    $response["subscribed_process"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["sp_id"] = $task["sp_id"];
        $tmp["aprocessid"] = $task["aprocessid"];
        $tmp["processname"] = $task["processname"];
        $tmp["recurrence"] = $task["recurrence"];
        $tmp["numrec"] = $task["numrec"];
        $tmp["euid"] = $task["euid"];
        $tmp["schedtype"] = $task["schedtype"];
        $tmp["startdate"] = $task["startdate"];
        $tmp["enddate"]= $task["enddate"];
        $tmp["datefinished"] = $task["datefinished"];
        $tmp["datesubscribed"] = $task["datesubscribed"];
        $tmp["agencyname"] = $task["agencyname"];
        $tmp["status"]  = $task["status"];


        array_push($response["subscribed_process"],$tmp);
    }

    echoResponse(200,$response);

});


/**
 * get the sp_requirements
 */

$app->get('/get_the_sp_requirements/:id', function($euid){

    $response = array();
    $response["error"] = FALSE;
    $result = get_the_sp_requirements($euid);
    $response["sp_requirements"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["sp_reqId"] = $task["sp_reqId"];
        $tmp["sp_id"] = $task["sp_id"];
        $tmp["reqname"] = $task["reqname"];
        $tmp["copy"] = $task["copy"];
        $tmp["notes"] = $task["notes"];
        $tmp["reqstatus"] = $task["reqstatus"];
        $tmp["reference_reqid"] = $task["reference_reqid"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["processid"] = $task["processid"];

        array_push($response["sp_requirements"],$tmp);
    }

    echoResponse(200,$response);

});

/**
 * get the sp steps
 */
$app->get('/get_the_sp_steps/:id', function($euid){

    $response = array();
    $response["error"] = FALSE;
    $result = get_the_sp_steps($euid);
    $response["sp_steps"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["sp_stepsid"] = $task["sp_stepsid"];
        $tmp["sp_id"] = $task["sp_id"];
        $tmp["stepdesc"] = $task["stepdesc"];
        $tmp["stepseqno"] = $task["stepseqno"];
        $tmp["stepstatus"] = $task["stepstatus"];
        $tmp["parentstepid"] = $task["parentstepid"];
        $tmp["reference_stepid"] = $task["reference_stepid"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["processid"] = $task["processid"];

        array_push($response["sp_steps"],$tmp);
    }

    echoResponse(200,$response);
});

/**
 * get sp steps copre
 */

$app->get('/sp_steps_copre/:id', function($userId){

    $response = array();
    $response["error"] = FALSE;
    $result = get_sp_step_copre($userId);
    $response["sp_steps_copre"] = array();

    foreach($result as $task){

        $tmp = array();

        $tmp["sp_CoPreid"] = $task["sp_CoPreid"];
        $tmp["sp_stepid"] = $task["sp_stepid"];
        $tmp["sp_prestepid"] = $task["sp_prestepid"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["processid"] = $task["processid"];
        $tmp["data_id"] = $task["data_id"];

        array_push($response["sp_steps_copre"],$tmp);
    }
    echoResponse(200,$response);

});


/**
 * GET THE SP STEPS REQUIREMENTS
 */
$app->get('/get_sp_steps_required/:id', function($userId){

    $response = array();
    $response["error"]=FALSE;
    $response["sp_steps_required"] =array();
    $result = get_sp_steps_required($userId);

    foreach($result as $task){

        $tmp = array();

        $tmp["sp_steprequired"] = $task["sp_steprequired"];
        $tmp["sp_reqid"] = $task["sp_reqid"];
        $tmp["sp_stepid"] = $task["sp_stepid"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["processid"] = $task["processid"];
        $tmp["data_id"] = $task["data_id"];

        array_push($response["sp_steps_required"],$tmp);
    }

    echoResponse(200,$response);
});

/**
 * search all agencies
 */
$app->get('/search_all_agencies/:keyword', function($keyword){

    $response = array();
    $response["error"] = false;
    $response["search_agencies"] = array();
    $search_result = searchAllAgencies($keyword);

    if($search_result == TRUE){

        foreach($search_result as $task){

            $tmp = array();

            $tmp["agencyid"] = $task["agencyid"];
            $tmp["agencyname"] = $task["agencyname"];
            $tmp["branch"] = $task["branch"];
            $tmp["address"] = $task["address"];
            $tmp["officeno"] = $task["officeno"];
            $tmp["contactperson"] = $task["contactperson"];
            $tmp["contactpersonno"] = $task["contactpersonno"];
            $tmp["contactemail"] = $task["contactemail"];
            $tmp["username"] = $task["username"];
            $tmp["password"] = $task["password"];
            $tmp["logo"] = 'http://192.168.8.100/PhpApiForAndroid/endinmind/agency/agencydashboard/files/'.$task["logo"];
            $tmp["about"]= $task["about"];
            $tmp["status"] = $task["status"];
            $tmp["lastdate_login"] = $task["lastdate_login"];


            array_push($response["search_agencies"],$tmp);
        }

        echoResponse(200,$response);

    }else{

        $response["error"] = TRUE;
        $response["message"] = "no search result found.";
        echoResponse(200,$response);
    }

});


/////updating the subscribed process record
$app->post('/update_the_subscribed_process', function() use ($app){


    $response = array("error" => FALSE);


    $aprocessid = $app->request->post('aprocessid');
    $processname = $app->request->post('processname');
    $recurrence = $app->request->post('recurrence');
    $numrec = $app->request->post('numrec');
    $euid = $app->request->post('euid');
    $schedtype = $app->request->post('schedtype');
    $startdate = $app->request->post('startdate');
    $enddate = $app->request->post('enddate');
    $datefinished = $app->request->post('datefinished');
    $datesubscribed = $app->request->post('datesubscribed');
    $agencyname = $app->request->post('agencyname');
    $status = $app->request->post('status');

    if(CheckUserStatus($euid))
    {
        if(check_the_subscribed_id($aprocessid,$euid)) {

            update_the_record_of_subscribed_process($aprocessid,$processname,$recurrence,
                $numrec,$euid, $schedtype,$startdate,
                $enddate, $datefinished, $datesubscribed,$agencyname,$status);


            $response["error"] = FALSE;
            $response["message"] = "Update completed.";
            echo json_encode($response);


        }else {

            add_the_record_of_subscribed_process($aprocessid,$processname,$recurrence,
                $numrec,$euid, $schedtype,$startdate,
                $enddate, $datefinished, $datesubscribed,$agencyname,$status);

            $response["error"] = FALSE;
            $response["message"] = "Add completed.";
            echo json_encode($response);
        }
    }else{

        $response["error"] = TRUE;
        $response["message"] = "Sorry! Your account is not Active.";
        echo json_encode($response);
    }



});




/////updating the subscribed process record
$app->post('/update_the_sp_requirements', function() use ($app){


    $response = array("error" => FALSE);

    $sp_id = $app->request->post('sp_id');
    $reqname = $app->request->post('reqname');
    $copy = $app->request->post('copy');
    $notes = $app->request->post('notes');
    $reqstatus = $app->request->post('reqstatus');
    $reference_reqid = $app->request->post('reference_reqid');
    $user_id = $app->request->post('user_id');
    $processid = $app->request->post('processid');


    if(CheckUserStatus($user_id))
    {
        if(check_the_sp_requirements_id($reference_reqid,$user_id)){

            UpdateSpRequirementsNow($sp_id,$reqname,$copy,$notes,$reqstatus,$reference_reqid,$user_id,$processid);
            $response["error"] = FALSE;
            $response["message"] = "updated.";
            echo json_encode($response);

        }else{
            add_the_sp_requirements($sp_id,$reqname,$copy,$notes,$reqstatus,$reference_reqid,$user_id,$processid);
            $response["error"] = FALSE;
            $response["message"] = "added.";
            echo json_encode($response);
        }
    }



});

/**
 * sp steps management
 */
$app->post('/update_sp_steps', function() use ($app){


    $response = array("error" => FALSE);

    $sp_id = $app->request->post('sp_id');
    $stepdesc = $app->request->post('stepdesc');
    $stepseqno = $app->request->post('stepseqno');
    $stepstatus = $app->request->post('stepstatus');
    $parentstepid = $app->request->post('parentstepid');
    $reference_stepid = $app->request->post('reference_stepid');
    $user_id = $app->request->post('user_id');
    $processid = $app->request->post('processid');



     if(CheckUserStatus($user_id))
    {
        if(check_the_id_of_sp_steps($reference_stepid,$user_id)) {

            update_the_sp_steps($sp_id, $stepdesc, $stepseqno, $stepstatus, $parentstepid, $reference_stepid, $user_id,$processid);
            $response["error"] = FALSE;
            $response["message"] = "sp steps updated";
            echo json_encode($response);

        }else{

            add_the_sp_steps($sp_id,$stepdesc,$stepseqno,$stepstatus,$parentstepid,$reference_stepid,$user_id,$processid);
            $response["error"] = FALSE;
            $response["message"] = "sp steps added";
            echo json_encode($response);
        }

    }

});

/**
 * update sp step copre
 */
$app->post('/update_sp_step_copries', function() use($app){


    $response = array("error" => FALSE);

    $sp_CoPreid = $app->request->post('sp_CoPreid');
    $sp_stepid = $app->request->post('sp_stepid');
    $sp_prestepid = $app->request->post('sp_prestepid');
    $user_id = $app->request->post('user_id');
    $processid = $app->request->post('processid');
    $data_id = $app->request->post('data_id');



    if(CheckUserStatus($user_id))
    {
        if(checkTheIdOfSpStepCopre($sp_CoPreid,$user_id)){

        }else if (checkTheIdOfSpStepCopreMobileDataId($data_id,$user_id)) {
        	# code...
        }
        else{
            add_sp_step_copries($sp_stepid,$sp_prestepid,$user_id,$processid,$data_id);
            $response["error"] = FALSE;
        }
    }


});

/**
 * update the sp steps required
 */
$app->post('/update_the_sp_steps_Required',function() use($app){

    $response = array("error" => FALSE);

    $sp_steprequired = $app->request->post('sp_steprequired');
    $sp_reqId = $app->request->post('sp_reqid');
    $sp_stepId = $app->request->post('sp_stepid');
    $userId = $app->request->post('user_id');
    $processId = $app->request->post('processid');
    $data_id = $app->request->post('data_id');

    

   
    if(CheckUserStatus($userId))
    {
        if(checkTheSpStepRequiredId($sp_steprequired,$userId)){

        }else if (checkTheSpStepRequiredMobileDataId($data_id,$userId)) {
        	
        }
        else{
            add_sp_stepRequired($sp_reqId, $sp_stepId,$userId,$processId,$data_id);
            $response["error"] = FALSE;
        }
    }
    


});




/**
 * view user subscriptions
 */
$app->get('/user_subscription/:id', function($userId){

    $response = array();
    $result = get_user_subscription($userId);
    $response["error"] = false;
    $response["subscriptions"] = array();



        foreach($result as $task){

            $tmp = array();


            $tmp["subID"] = $task["subID"];
            $tmp["subscribedby"] = $task["subscribedby"];
            $tmp["planid"] = $task["planid"];
            $tmp["numsubscription"] = $task["numsubscription"];
            $tmp["totalamount"] = $task["totalamount"];
            $tmp["paypalactno"] = $task["paypalactno"];
            $tmp["dateapplied"] = $task["dateapplied"];
            $tmp["startdate"] = $task["startdate"];
            $tmp["enddate"] = $task["enddate"];
            $tmp["status"] = $task["status"];

            array_push($response["subscriptions"],$tmp);
        }
        echoResponse(200,$response);




});


/**
 * get all personal notifications
 */
$app->get('/get_personal_notifications/:id',function($userId){

    $response = array();
    $result = get_personal_notifications($userId);
    $response["error"] = false;
    $response["personal_notifications"] = array();



        foreach($result as $task){
            $tmp = array();

            $tmp["p_notificationId"] = $task["p_notificationId"];
            $tmp["euid"] = $task["euid"];
            $tmp["notification_detail"] = $task["notification_detail"];
            $tmp["processid"] = $task["processid"];
            $tmp["processname"] = $task["processname"];
            $tmp["notification_from"] = $task["notification_from"];
            $tmp["datecreated"] = $task["datecreated"];
            $tmp["status"] = $task["status"];
            $tmp["process_type"] = $task["process_type"];

            array_push($response["personal_notifications"],$tmp);
        }
        echoResponse(200,$response);

});




$app->get('/get_agency_notifications/:id',function($userId){

    $response = array();
    $result = get_agency_notifications($userId);
    $response["error"] = false;
    $response["agency_notifications"] = array();



        foreach($result as $task){
            $tmp = array();

            $tmp["p_notificationId"] = $task["p_notificationId"];
            $tmp["euid"] = $task["euid"];
            $tmp["notification_detail"] = $task["notification_detail"];
            $tmp["processid"] = $task["processid"];
            $tmp["processname"] = $task["processname"];
            $tmp["notification_from"] = $task["notification_from"];
            $tmp["datecreated"] = $task["datecreated"];
            $tmp["status"] = $task["status"];
            $tmp["process_type"] = $task["process_type"];

            array_push($response["agency_notifications"],$tmp);
        }
        echoResponse(200,$response);

});

$app->post('/delete_subscribed_process', function() use($app){

    $userId = $app->request->post('euid');
    $aprocessId = $app->request->post('aprocessid');

    $response = array("error" => FALSE);

    delete_subscribed_process($userId,$aprocessId);

    $response["error"] = FALSE;
    $response["message"] = "subscribed process deleted.";
    echoResponse(200,$response);


});



$app->post('/delete_sp_requirements', function() use($app){

    $userId = $app->request->post('user_id');
    $aprocessId = $app->request->post('processid');

    $response = array("error" => FALSE);

    delete_sp_requirements($userId,$aprocessId);

    $response["error"] = FALSE;
    $response["message"] = "requirements process deleted.";
    echoResponse(200,$response);


});


$app->post('/delete_sp_steps', function() use($app){


    $userId = $app->request->post('user_id');
    $aprocessId = $app->request->post('processid');

    $response = array("error" => FALSE);
    delete_sp_steps($userId,$aprocessId);

    $response["error"] = FALSE;
    $response["message"] = "sp steps process deleted.";
    echoResponse(200,$response);

});


$app->post('/delete_sp_steps_required', function() use($app){


    $userId = $app->request->post('user_id');
    $aprocessId = $app->request->post('processid');


    $response = array("error" => FALSE);
    sp_step_required($userId,$aprocessId);

    $response["error"] = FALSE;
    $response["message"] = "sp steps required process deleted.";
    echoResponse(200,$response);

});

$app->post('/delete_sp_steps_copre', function() use($app){

    $userId = $app->request->post('user_id');
    $aprocessId = $app->request->post('processid');


    $response = array("error" => FALSE);
    sp_steps_repo($userId,$aprocessId);

    $response["error"] = FALSE;
    $response["message"] = "sp steps repo process deleted.";
    echoResponse(200,$response);
});
$app->post('/download_agency_process_by_process_id', function() use($app){

    $response = array("error" => FALSE);

    $userId = $app->request->post('euid');
    $processId = $app->request->post('aprocessid');


    $subscribedinfo =  download_agency_process_using_processId($processId,$userId);

    if($subscribedinfo){
        $aprocessid = $processId;
        $processname = $subscribedinfo['processname'];
        $recurrence = $subscribedinfo['recurrence'];
        $numrec = $subscribedinfo['numrec'];
        $euid = $userId;
        $schedtype = "";
        $startdate = "";
        $enddate = "";
        $datefinished = "";
        $datesubscribed = "";
        $agencyname = $subscribedinfo['agencyname'];
        $status = 'Active';
    }
    DownloadAgencyProcess($aprocessid,$processname,$recurrence,$numrec,$euid,$schedtype,
        $startdate,$enddate,$datefinished,$datesubscribed,$agencyname,$status);

    $response["error"] = FALSE;
    $response["message"] = "ok";
    echo json_encode($response);

});


$app->post('/change_notification_status', function() use($app){

    $response = array("error" => FALSE);



    $userId = $app->request->post('euid');
    $processId = $app->request->post('processid');
    $status = $app->request->post('status');

    change_notification_status($userId,$processId,$status);
    $response["error"] = FALSE;
    $response["message"] = "update success";
    echo json_encode($response);
});


/**
 * get recurrence
 */
$app->get('/get_recurrence_by_user_id/:id', function($userId){

	$response = array();
	$result = get_recurrence($userId);
    $response["error"] = false;
    $response["recurrence"] = array();



        foreach ($result as $task ) {

            $tmp = array();

            $tmp["rec_notificationid"] = $task["rec_notificationid"];
            $tmp["processid"] = $task["processid"];
            $tmp["processname"] = $task["processname"];
            $tmp["process_from"] = $task["process_from"];
            $tmp["notify_for"] = $task["notify_for"];
            $tmp["notification_message"] = $task["notification_message"];
            $tmp["datenotify"] = $task["datenotify"];
            $tmp["status"] = $task["status"];

            array_push($response["recurrence"],$tmp);
        }
        echoResponse(200,$response);
});


$app->get('/get_rec_no_details_by_nId/:id', function($notificationId){

    $response = array();
    $result = fetch_rec_notification_by_nId($notificationId);
    $response["error"] = false;
    $response["recurrence_details"] = array();

    foreach ($result as $task ) {

        $tmp = array();

        $tmp["rec_notificationid"] = $task["rec_notificationid"];
        $tmp["processid"] = $task["processid"];
        $tmp["processname"] = $task["processname"];
        $tmp["process_from"] = $task["process_from"];
        $tmp["notify_for"] = $task["notify_for"];
        $tmp["notification_message"] = $task["notification_message"];
        $tmp["datenotify"] = $task["datenotify"];
        $tmp["status"] = $task["status"];

        array_push($response["recurrence_details"],$tmp);
    }
    echoResponse(200,$response);


});

$app->post('/change_recurence_notification_status', function() use($app){

    $response = array("error" => FALSE);

    $status = $app->request->post('status');
    $rec_notificationid = $app->request->post('rec_notificationid');

    change_recurrence_notification_status($rec_notificationid,$status);
    $response["error"] = FALSE;
    $response["message"] = "notification status changed.";
    echo json_encode($response);
});

$app->run();




































