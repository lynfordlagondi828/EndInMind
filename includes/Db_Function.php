<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 6/27/2016
 * Time: 3:06 PM
 */

function DBConnect(){

    try{
        return new PDO('mysql:host=localhost;dbname=endinmind','root','');
    }catch (PDOException $ex){
        echo "Could not connect Database: " . $ex->getMessage();
    }
}

function loginUser($username,$password){

    $status = "Active";
    $database = DBConnect();
    $password = md5($password);
    $sql = "select * from enduser where username = ? AND password = ? AND status = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($username,$password,$status));
    $rowId = $stmt->fetch();
    $database = null;
    return $rowId;
}



/**
 * get Process By userId
 * @param $userId
 * @return array
 */
function getProcessByUserId($userId){


    $database = DBConnect();
    $status='Deactivated';
    $sql="SELECT * FROM euprocess WHERE euid = ?  AND status != ? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$status));
    $rowId = $stmt->fetchAll();
    $database = null;
    return $rowId;
}

/**
 * get requirements by userId
 * @param $userId
 * @return array
 */
function getRequirementsByUserId($userId){

    $database = DBConnect();
    $reqStatus='Deactivated';

    $sql ="SELECT * FROM  requirements WHERE user_id = ? AND reqStatus != ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$reqStatus));
    $rowId = $stmt->fetchAll();
    $database = null;
    return $rowId;
}


/**
 * get steps by userId
 * @param $userId
 * @return array
 */
function getStepsByUserId($userId){

    $database = DBConnect();
    $stepstatus='Deactivated';

    $sql ="SELECT * FROM  steps WHERE user_id = ? AND stepstatus != ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$stepstatus));
    $rowId = $stmt->fetchAll();
    $database = null;
    return $rowId;
}

/**
 * get stepcopre
 */
function getStepCopre($userId){

    $database = DBConnect();
    $sql = "SELECT * FROM stepcopre WHERE user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId));
    $rowId = $stmt->fetchAll();
    $database = null;
    return $rowId;
}

/**
 * get step required
 */
function getStepRequired($userId){

    $database = DBConnect();
    $sql = "SELECT * FROM steprequired WHERE user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId));
    $rowId = $stmt->fetchAll();
    $database = null;
    return $rowId;
}

/**
 * Add Process
 * @param type $euprocessid
 * @param type $pname
 * @param type $schedType
 * @param type $start_date
 * @param type $date_end
 * @param type $dateFinished
 * @param type $euid
 * @param type $recurrence
 * @param type $numrec
 * @param type $date_create
 * @param type $date_modified
 * @param type $agency
 * @param type $status
 */
function AddProcess($euprocessid,$pname,$schedType,$start_date,$date_end,$dateFinished,
                   $euid,$recurrence,$numrec,$date_create,$date_modified,$agency,$status){
    
    
    $database = DBConnect();
    
    $sql = "insert into euprocess(euprocessid,processname,schedtype,startdate,enddate,datefinished,euid,recurrence,
        numrec,datecreated,datemodified,agencycopiedfrom,status)Values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt  = $database->prepare($sql);
    $stmt->execute(array($euprocessid,$pname,$schedType,$start_date,$date_end,$dateFinished,
                   $euid,$recurrence,$numrec,$date_create,$date_modified,$agency,$status));

    if($status == 'Completed' AND ($recurrence == "monthly" || $recurrence == "yearly")) {
        personal_rec_notification($euprocessid,$euid,$dateFinished,$pname,$recurrence,$numrec);
    }


    $database = null;
}


function CheckTheProcessId($euprocessId,$userId){
    
    $database = DBConnect();
   
    $sql = "select * from  euprocess where  euprocessid = ? AND euid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($euprocessId,$userId));
    $row = $stmt->fetch();
    $database = null;
    return $row;
}

function UpdateSyncData($euprocessid,$pname,$schedType,$start_date,$date_end,$dateFinished,
                   $euid,$recurrence,$numrec,$agency,$status){
    
    $database = DBConnect();
    
    $sql = "update euprocess set processname = ?,schedtype = ?,startdate = ?,  enddate = ?, 
            datefinished = ?,  recurrence = ?,  numrec = ?,  agencycopiedfrom = ?,
             status = ? where euprocessid = ? AND euid = ? ";

    $stmt = $database->prepare($sql);
    $stmt->execute(array($pname,$schedType,$start_date,$date_end,$dateFinished,
                   $recurrence,$numrec,$agency,$status,$euprocessid,$euid));
    

    // FUNCTION ADDING RECURRENCE NOTIFICATION

    if($status == 'Completed' AND ($recurrence == "monthly" || $recurrence == "yearly")) {
        personal_rec_notification($euprocessid,$euid,$dateFinished,$pname,$recurrence,$numrec);
    }

    $database = null;
    
}

function CheckDateIfUpdated($euprocessid){
    
    $database = DBConnect();
   
    $sql = "select datemodified from  euprocess where  euprocessid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($euprocessid));
    $row = $stmt->fetch();
    $database = null;
    return $row;
}

function CheckUserStatus($euid)
{
    $database = DBConnect();
    $status = 'Active';
    $sql = "select * from enduser where euid = ? AND status = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($euid,$status));
    $row = $stmt->fetch();
    $database = null;
    return $row;
}




//add new new
function addNewRequirements($reqname,$copy,$notes,$reqStatus,$processid,$user_id,$mobile_requirementsid){

    $database = DBConnect();
    $sql = "insert into requirements(reqname,copy,notes,reqStatus,processid,user_id,mobile_requirementsid)Values(?,?,?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($reqname,$copy,$notes,$reqStatus,$processid,$user_id,$mobile_requirementsid));
    $database = null;
}


/**
*Check requirements ids from mobile
*/
function CheckReqIdFromMobile($mobile_requirementsid,$userId){

    $database = DBConnect();
    $sql = "select * from requirements where mobile_requirementsid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($mobile_requirementsid,$userId));
    $row = $stmt->fetch();
    $database = null;
    return $row;
}


//update requirements from mobile
function UpdateRequirementsFromMobile($reqname,$copy,$notes,$reqStatus,$mobile_requirementsid,$userId){

    $database = DBConnect();

    $sql = "update requirements set reqname = ?, copy = ?, notes = ?, reqStatus = ? where mobile_requirementsid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($reqname,$copy,$notes,$reqStatus,$mobile_requirementsid,$userId));
    $database = null;
}


/**
*check id from web
*/
function CheckReqIdFromWeb($reqid,$userId){

    $database = DBConnect();
    $sql = "select * from requirements where reqid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($reqid,$userId));
    $row = $stmt->fetch();
    $database = null;
    return $row;
}



function UpdateRequirementsFromWeb($reqname,$copy,$notes,$reqStatus,$reqid,$userId){

    $database = DBConnect();

    $sql = "update requirements set reqname = ?, copy = ?, notes = ?, reqStatus = ? where reqid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($reqname,$copy,$notes,$reqStatus,$reqid,$userId));
    $database = null;
}

/**
 * sync steps
 */
function SyncSteps($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id,$mobile_created_id){
    
    $database = DBConnect();
    
    $sql = "insert into steps(stepdesc,stepseqno,stepstatus,parentstepid,processid,user_id,mobile_created_id)Values(?,?,?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id,$mobile_created_id));
    $database = null;
}

/*
 * check the step id of primary key
 */
function checkTheIdOfStepsPrimaryKey($stepid,$userId){
    
    $database = DBConnect();
    $sql = "select stepid from steps where stepid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($stepid,$userId));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}


/**
 * the mobile id's
 */
function checkTheMobileIdOfSteps($mobile_created_id,$userId){
    
    $database = DBConnect();
    $sql = "select * from steps where mobile_created_id = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($mobile_created_id,$userId));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

/**
 * function update steps
 */
function UpdateStepsFromMobile($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id,$mobile_created_id){
    
    $database = DBConnect();
    $sql = "update steps set stepdesc = ?, stepseqno = ?, stepstatus = ?, parentstepid = ?, processid = ? where mobile_created_id = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $mobile_created_id,$user_id));
    $database = null;
}

/*
* update steps from web
*/
function UpdateStepsFromWeb($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid, $user_id,$stepid){
    
    $database = DBConnect();
    $sql = "update steps set stepdesc = ?, stepseqno = ?, stepstatus = ?, parentstepid = ?, processid = ? where stepid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($stepdesc, $stepseqno, $stepstatus, $parentstepid, $processid,$stepid,$user_id));
    $database = null;
}

/**
 * add step copre
 */
function AddStepCopre($stepId,$prestepid,$userId,$agencyprocessid,$data_id,$data_id){

    $database = DBConnect();
    $sql = "INSERT INTO stepcopre(stepid,prestepid,user_id,agencyprocessid,data_id)VALUES(?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($stepId,$prestepid,$userId,$agencyprocessid,$data_id));
    $database = null;

}

/**
 * check the step copre
 *
 */
function checkTheStepCopre($copreid){
    $database = DBConnect();
    $sql = "SELECT * FROM stepcopre WHERE copreid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($copreid));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

function checkTheStepCopreMobileDataId($data_id){
    $database = DBConnect();
    $sql = "SELECT * FROM stepcopre WHERE data_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($data_id));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

/**
 * add step required
 */
function AddStepRequired($stepid,$reqid,$userId,$agencyprocessid,$data_id){

    $database = DBConnect();
    $sql = "INSERT INTO steprequired(stepid,reqid,user_id,agencyprocessid,data_id)VALUES(?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($stepid,$reqid,$userId,$agencyprocessid,$data_id));
    $database = null;

}

/**
 * check the step required id
 */
function checkTheStepRequiredId($steprequiredid){

    $database = DBConnect();
    $sql = "SELECT * FROM steprequired WHERE steprequiredid = ? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($steprequiredid));
    $result = $stmt->fetch();
    $database = null;
    return $result;

}

/**
 * check the step required data id
 */
function checkTheStepRequiredDataId($data_id){

    $database = DBConnect();
    $sql = "SELECT * FROM steprequired WHERE data_id = ? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($data_id));
    $result = $stmt->fetch();
    $database = null;
    return $result;

}


/**
 * Display all agencies
 * @return array
 */
function DisplayAllAgency(){

    $database = DBConnect();

    //status
    $status = "Deactivated";

    $sql = "SELECT * FROM agencies WHERE status !=?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($status));
    $row = $stmt->fetchAll();
    $database = null;
    return $row;

}


//view agency process by agency id
function ViewAgencyProcess($agencyId){

    $database = DBConnect();

    $status = "Deactivated";
    $unlaunchStatus = "Unlaunch";

    $sql  = "SELECT ap.*,a.agencyname,a.logo FROM agencyprocess ap JOIN agencies a ON ap.agencyid = a.agencyid WHERE ap.agencyid = ? AND ap.status != ? AND ap.status !=? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($agencyId,$status,$unlaunchStatus));
    $row = $stmt->fetchAll();
    $database = null;
    return $row;
}


//view agency process details
function AgencyProcessDetails($aprocessid){

    $database = DBConnect();

    $status = "Deactivated";

    $sql = "SELECT * FROM agencyprocess WHERE aprocessid = ? AND status != ? ";

    $stmt = $database->prepare($sql);
    $stmt->execute(array($aprocessid,$status));
    $row = $stmt->fetchAll();
    $database = null;
    return $row;
}


/**
 * Download the agency process
 * @param $aprocessId
 * @param $processname
 * @param $recurrece
 * @param $numrec
 * @param $euid
 * @param $schedType
 * @param $startdate
 * @param $enddate
 * @param $datefinished
 * @param $datesubscribed
 * @param $agencyname
 * @param $status
 */
function DownloadAgencyProcess($aprocessId, $processname, $recurrece,
    $numrec, $euid,$schedType, $startdate, $enddate, $datefinished,$datesubscribed,
    $agencyname,$status){

    $database = DBConnect();

    $sql = "INSERT INTO subscribedprocess(aprocessid,processname,recurrence,numrec,euid,
              schedtype,startdate,enddate,datefinished,datesubscribed,agencyname,status)VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($aprocessId, $processname, $recurrece,
        $numrec, $euid,$schedType, $startdate, $enddate, $datefinished,$datesubscribed,
        $agencyname,$status));

    //GET LAST SUBSCRIBED PROCESS THE DOWNLOAD STEPS AND REQUIRMENT CONTENT
    $getsp_id =  getLastSubscribedId($euid);
    $sp_id = $getsp_id['sp_id'];
    $stepreqinfo['euid'] = $euid;
    $stepreqinfo['sp_id'] = $sp_id;
    $stepreqinfo['processid'] = $aprocessId;
    addAllRequirementsSubscribedProcess($stepreqinfo);
    addAllStepsSubscribedProcess($stepreqinfo);

    // ADD NOTIFICATIONS TO THE AGENCY
    $agencyinfo = get_agencyid($aprocessId);
    $agencyid = $agencyinfo['agencyid'];
    $notification_for = "Agency";
    $notification_detail = "New Process Subscriber";
    $notification_link = "listsubscribers.php";
    $notification_from = "Personal";
    $status = "Unread";
    $sql2 = "INSERT INTO notifications(notification_for,notificationfor_id,notification_detail,notification_link,notification_userid,notification_from,datecreated,status) VALUES(?,?,?,?,?,?,now(),?)";
    $st2=$database->prepare($sql2);
    $st2->execute(array($notification_for,$agencyid,$notification_detail,$notification_link,$euid,$notification_from,$status));







    $database = null;

}


/**
 *check the agency process id
 */
function CheckAgencyId($aprocessId,$euid){

    $database = DBConnect();
    $sql = "select aprocessid from subscribedprocess where aprocessid = ? AND euid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($aprocessId,$euid));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}
//SUBSCRIBED REQUIREMENTS
function getLastSubscribedId($euid){
    $db = DBConnect();
    $sql = "SELECT sp_id FROM subscribedprocess WHERE euid = ? ORDER BY sp_id DESC LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute(array($euid));
    $eu = $st->fetch();
    $db = null;
    return $eu;
}
function addAllRequirementsSubscribedProcess($stepreqinfo){
    $db = DBConnect();
    $sp_id = $stepreqinfo['sp_id'];
    $processid = $stepreqinfo['processid'];
    $euid = $stepreqinfo['euid'];
    $req = getAllRequirementsSubscribedProcess($processid);
    if($req){

        foreach($req as $reqinfo){

            $status = "Active";
            $reqname = $reqinfo['reqname'];
            $copy = $reqinfo['copy'];
            $notes = $reqinfo['notes'];
            $reqid = $reqinfo['reqid'];
            $sql =  "INSERT INTO sp_requirements(sp_id,reqname, copy, notes, reqstatus,reference_reqid,user_id,processid) VALUES (?,?,?,?,?,?,?,?)";
            $st = $db->prepare($sql);
            $st->execute(array($sp_id,$reqname,$copy,$notes,$status,$reqid,$euid,$processid));
        }
    }
    $db = null;
}
function getAllRequirementsSubscribedProcess($apdetailsid){
    $db = DBConnect();
    $status = 'Deactivated';
    $sql = "SELECT * FROM requirements WHERE processid = ? AND reqStatus != ?";
    $st = $db->prepare($sql);
    $st->execute(array($apdetailsid,$status));
    $eu = $st->fetchAll();
    $db = null;
    return $eu;
}
// GET ALL STEPS CONTENT FROM DOWNLOADED PROCESS
// GET ALL STEPS FOR SUBSCRIBED PROCESS

function addAllStepsSubscribedProcess($stepreqinfo){
    $db = DBConnect();
    $sp_id = $stepreqinfo['sp_id'];
    $processid = $stepreqinfo['processid'];
    $euid = $stepreqinfo['euid'];
    $step = getAllStepsSubscribedProcess($processid);
    if($step){

        foreach($step as $stepinfo){

            $status = "Active";
            $stepdesc = $stepinfo['stepdesc'];
            $stepno = $stepinfo['stepseqno'];
            $stepid = $stepinfo['stepid'];
            $parentstepid = $stepinfo['parentstepid'];
            $sql =  "INSERT INTO sp_steps(sp_id,stepdesc,stepseqno,stepstatus,parentstepid,reference_stepid,user_id,processid) VALUES (?,?,?,?,?,?,?,?)";
            $st = $db->prepare($sql);
            $st->execute(array($sp_id,$stepdesc,$stepno,$status,$parentstepid,$stepid,$euid,$processid));
            $steprequired = getAllStepRequiredSubscribedProcess($stepid);
            if($steprequired){
                foreach($steprequired as $sr){
                    $sp_reqid = $sr['reqid'];
                    $sp_stepid = $sr['stepid'];
                    $sql1 =  "INSERT INTO sp_stepsrequired(sp_reqid,sp_stepid,user_id,processid) VALUES (?,?,?,?)";
                    $st1 = $db->prepare($sql1);
                    $st1->execute(array($sp_reqid,$sp_stepid,$euid,$processid));
                }
            }
            $stepcopre = getAllStepcopreSubscribedProcess($stepid);
            if($stepcopre){
                foreach($stepcopre as $sc){

                    $sp_stepid = $sc['stepid'];
                    $sp_prestepid = $sc['prestepid'];
                    $sql2 =  "INSERT INTO sp_stepcopre(sp_stepid,sp_prestepid,user_id,processid) VALUES (?,?,?,?)";
                    $st2 = $db->prepare($sql2);
                    $st2->execute(array($sp_stepid,$sp_prestepid,$euid,$processid));
                }
            }

        }
    }
    $db = null;
}
function getAllStepsSubscribedProcess($stepid){
    $db = DBConnect();
    $status = "Deactivated";
    $sql = "SELECT * FROM steps WHERE processid = ? AND stepstatus != ?";
    $st = $db->prepare($sql);
    $st->execute(array($stepid,$status));
    $eu = $st->fetchAll();
    $db = null;
    return $eu;
}
function getAllStepRequiredSubscribedProcess($stepid){
    $db = DBConnect();
    $sql = "SELECT * FROM steprequired WHERE stepid = ?";
    $st = $db->prepare($sql);
    $st->execute(array($stepid));
    $eu = $st->fetchAll();
    $db = null;
    return $eu;
}
function getAllStepcopreSubscribedProcess($stepid){
    $db = DBConnect();
    $sql = "SELECT * FROM stepcopre WHERE stepid = ?";
    $st = $db->prepare($sql);
    $st->execute(array($stepid));
    $eu = $st->fetchAll();
    $db = null;
    return $eu;
}


//get the downloaded subcribed process and store to local database
function get_and_download_subscribed_process($euid){

    $database = DBConnect();
    $sql = "SELECT * FROM subscribedprocess WHERE euid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($euid));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;
}

//get the sp requirements
function get_the_sp_requirements($euid){

    $database = DBConnect();

    $sql = "SELECT  * FROM sp_requirements WHERE user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($euid));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;
}


//get the sp steps
function get_the_sp_steps($euid){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_steps WHERE user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($euid));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;
}


//get sp setpcopre
function get_sp_step_copre($user_id){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_stepcopre WHERE user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($user_id));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;

}

//get sp step required
function get_sp_steps_required($user_id){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_stepsrequired WHERE user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($user_id));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;
}



/**
 * search all agencies
 */
function searchAllAgencies($keyword){

    $keyword = '%'. $keyword . '%';
    $status = "Deactivated";


    $database = DBConnect();
    $sql = "SELECT * FROM agencies WHERE agencyname LIKE ? OR branch LIKE ? OR address LIKE ? AND status !=?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($keyword,$keyword,$keyword,$status));
    $search_result = $stmt->fetchAll();
    $database = null;
    return $search_result;
}


//////start for synchronization for subscribed process////////////////////////////

/**
 * check the the subscribed id first to before executing the the process
 */
function check_the_subscribed_id($aprocessid,$userId){

    $database = DBConnect();

    $sql = "SELECT * FROM subscribedprocess WHERE aprocessid =? AND euid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($aprocessid,$userId));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

/**
 * if the id found update the record of subscribed process
 */
function update_the_record_of_subscribed_process($aprocessid,$processname,$recurrence,
                                                 $numrec,$euid, $schedtype,$startdate,
                                                $enddate, $datefinished, $datesubscribed,$agencyname,$status){


    $database = DBConnect();
    $sql = "UPDATE subscribedprocess set  processname = ?, recurrence = ?, numrec = ?, schedtype = ?,
            startdate = ?, enddate = ?, datefinished = ?, datesubscribed = ?, agencyname = ?, status = ? WHERE euid = ? AND aprocessid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($processname,$recurrence,
        $numrec, $schedtype,$startdate,
        $enddate, $datefinished, $datesubscribed,$agencyname,$status,$euid,$aprocessid));

    if($status == 'Completed' AND ($recurrence == "monthly" || $recurrence == "yearly")) {
        rec_notification($aprocessid,$euid,$datefinished,$processname,$recurrence,$numrec);
    }
    $database = null;

}

/**
 * if the id found update the record of subscribed process
 */
function add_the_record_of_subscribed_process($aprocessid,$processname,$recurrence,
                                                 $numrec,$euid, $schedtype,$startdate,
                                                 $enddate, $datefinished, $datesubscribed,$agencyname,$status){


    $database = DBConnect();
    $sql = "INSERT INTO subscribedprocess(aprocessid,processname, recurrence, numrec, euid, schedtype,
            startdate, enddate, datefinished, datesubscribed, agencyname,status)VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($aprocessid,$processname,$recurrence,
        $numrec,$euid, $schedtype,$startdate,
        $enddate, $datefinished, $datesubscribed,$agencyname,$status));

    if($status == 'Completed' AND ($recurrence == "monthly" || $recurrence == "yearly")) {
        rec_notification($aprocessid,$euid,$datefinished,$processname,$recurrence,$numrec);
    }
    $database = null;

}



/**
 * check the the sp_requirements
 */
function check_the_sp_requirements_id($reference_reqid,$user_id){

    $database = DBConnect();

    $sql = "SELECT * FROM sp_requirements WHERE reference_reqid =? AND user_id = ? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($reference_reqid, $user_id));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

/**
 * update the sp requirements
 */
function UpdateSpRequirementsNow($sp_id,$reqname,$copy,$notes,$reqstatus,$reference_reqid,$user_id,$processid){


    $database = DBConnect();
    $sql = "UPDATE sp_requirements set sp_id = ?, reqname = ?, copy = ?, notes = ?, reqstatus = ?, processid = ? WHERE reference_reqid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_id,$reqname,$copy,$notes,$reqstatus,$processid,$reference_reqid,$user_id));
    $database = null;
}


function add_the_sp_requirements($sp_id,$reqname,$copy,$notes,$reqstatus,$reference_reqid,$user_id,$processid){

    $database = DBConnect();
    $sql = "INSERT INTO sp_requirements(sp_id,reqname,copy,notes,reqstatus,reference_reqid,user_id,processid)VALUES(?,?,?,?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_id,$reqname,$copy,$notes,$reqstatus,$reference_reqid,$user_id,$processid));
    $database = null;

}



/**
 * sync management for sp steps
 */
function check_the_id_of_sp_steps($reference_stepid,$user_id){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_steps WHERE reference_stepid = ? AND user_id = ? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($reference_stepid,$user_id));
    $result = $stmt->fetch();
    $database = null;
    return $result;

}

/**
 * update the sp steps
 */
function update_the_sp_steps($sp_id,$stepdesc,$stepseqno,$stepstatus,$parentstepid,$reference_stepid,$user_id,$processid){

    $database = DBConnect();
    $sql = "UPDATE sp_steps set sp_id = ?, stepdesc = ?, stepseqno = ?, stepstatus = ?, parentstepid = ?, processid = ? WHERE reference_stepid = ? AND user_id = ? ";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_id,$stepdesc,$stepseqno,$stepstatus,$parentstepid,$processid,$reference_stepid,$user_id));
    $database = null;
}

function add_the_sp_steps($sp_id,$stepdesc,$stepseqno,$stepstatus,$parentstepid,$reference_stepid,$user_id,$processid){

    $database = DBConnect();
    $sql = "INSERT INTO sp_steps(sp_id,stepdesc,stepseqno,stepstatus,parentstepid,reference_stepid,user_id,processid)VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_id,$stepdesc,$stepseqno,$stepstatus,$parentstepid,$reference_stepid,$user_id,$processid));
    $database = null;
}


/**
 * update sp_step_copre
 */
function checkTheIdOfSpStepCopre($sp_CoPreid,$userId){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_stepcopre WHERE sp_CoPreid = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_CoPreid,$userId));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

function checkTheIdOfSpStepCopreMobileDataId($data_id,$userId){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_stepcopre WHERE data_id = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($data_id,$userId));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

/**
 * add sp step copries
 */
function add_sp_step_copries($sp_stepid,$sp_prestepid,$user_id,$processid,$data_id){

    $database = DBConnect();
    $sql = "INSERT INTO sp_stepcopre(sp_stepid,sp_prestepid,user_id,processid,data_id)VALUES (?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_stepid,$sp_prestepid,$user_id,$processid,$data_id));
    $database = null;
}

/**
 * check sp step required id
 */
function checkTheSpStepRequiredId($sp_steprequired,$userId){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_stepsrequired WHERE sp_steprequired = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_steprequired,$userId));
    $result = $stmt->fetch();
    $database = null;
    return  $result;
}


function checkTheSpStepRequiredMobileDataId($data_id,$userId){

    $database = DBConnect();
    $sql = "SELECT * FROM sp_stepsrequired WHERE data_id = ? AND user_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($data_id,$userId));
    $result = $stmt->fetch();
    $database = null;
    return  $result;
}

function add_sp_stepRequired($sp_reqId, $sp_stepId,$userId,$processId,$data_id){

    $database = DBConnect();
    $sql = "INSERT INTO sp_stepsrequired(sp_reqid,sp_stepid,user_id,processid,data_id)VALUES (?,?,?,?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($sp_reqId,$sp_stepId,$userId,$processId,$data_id));
    $database = null;
}


/**
 * view user subscriptions
 */
function get_user_subscription($userId){

    $database = DBConnect();
    $sql = "SELECT * FROM subscriptions WHERE subscribedby = ? AND status = 'Active'";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;

}
// PULL ALL PERSONAL NOTIFICATION FROM PERSONAL PROCESS AND AGENCY PROCESS


// ADD RECURRENCE NOTIFICATION



// SAMPLE SUBSCRIBED PROCESS

function  rec_notification($aprocessid, $euid,$datefinished,$processname,$recurrence,$numrec)
{
    $db = DBConnect();
    $processid = $aprocessid;
    $userid = $euid;
    $datefinished = $datefinished;
    $status = "Unread";
    $process_from = "Subscribed Process";
    $processname = $processname;
    $recurrence = $recurrence;
    $numrec = $numrec;

    // GET USER INFO
    $userinfo =  user($userid);
    $username = $userinfo['firstname'];

        $date = date_create("$datefinished");
        $notify_before_week = 7;
        $notify_before_month = 1;
        if($recurrence == 'yearly')
        {
            date_add($date,date_interval_create_from_date_string("$numrec year"));
            $dateend = date_format($date,'Y-m-d');
            $datenotify = date_create("$dateend");
            // NOTIFY BEFORE 1 WEEK

            date_sub($datenotify,date_interval_create_from_date_string("$notify_before_week day"));
            $datenotify_week = date_format($datenotify,'Y-m-d');
            $notification_message =  "Hi ". $username .  " maybe you will perform this process again a week from now";
            $sql = "INSERT INTO recurrence_notification(processid,processname,process_from,notify_for,notification_message,datenotify,status) VALUES(?,?,?,?,?,?,?)";
            $st = $db->prepare($sql);
            $st->execute(array($processid,$processname,$process_from,$userid,$notification_message,$datenotify_week,$status));

            // NOTIFY BEFORE 1 MONTH
            $datenotify2 = date_create("$dateend");
            date_sub($datenotify2,date_interval_create_from_date_string("$notify_before_month month"));
            $datenotify_month = date_format($datenotify2,'Y-m-d');
            $notification_message =  "Hi ". $username .  " maybe you will perform this process again a month from now";
            $sql2 = "INSERT INTO recurrence_notification(processid,processname,process_from,notify_for,notification_message,datenotify,status) VALUES(?,?,?,?,?,?,?)";
            $st2 = $db->prepare($sql2);
            $st2->execute(array($processid,$processname,$process_from,$userid,$notification_message,$datenotify_month,$status));


        }
        else
        {
            date_add($date,date_interval_create_from_date_string("$numrec month"));
            $dateend = date_format($date,'Y-m-d');
            $datenotify = date_create("$dateend");
            // NOTIFY BEFORE 1 WEEK
            date_sub($datenotify,date_interval_create_from_date_string("$notify_before_week day"));
            $dateend = date_format($date,'Y-m-d');
            $datenotify = date_create("$dateend");
            // NOTIFY BEFORE 1 WEEK

            date_sub($datenotify,date_interval_create_from_date_string("$notify_before_week day"));
            $datenotify_week = date_format($datenotify,'Y-m-d');
            $notification_message =  "Hi ". $username .  " maybe you will perform this process again a week from now";
            $sql3 = "INSERT INTO recurrence_notification(processid,processname,process_from,notify_for,notification_message,datenotify,status) VALUES(?,?,?,?,?,?,?)";
            $st3 = $db->prepare($sql3);
            $st3->execute(array($processid,$processname,$process_from,$userid,$notification_message,$datenotify_week,$status));

        }


    $db=null;
}
// personal add recurrence notification
function  personal_rec_notification($euprocessid,$euid,$dateFinished,$pname,$recurrence,$numrec)
{
    $db = DBConnect();
    $processid = $euprocessid;
    $userid = $euid;

    $datefinished = $dateFinished;
    $status = "Unread";
    $process_from = "Personal Process";
    $processname = $pname;
    $recurrence = $recurrence;
    $numrec = $numrec;

    // GET USER INFO
    $userinfo =  user($userid);
    $username = $userinfo['firstname'];


    $date = date_create("$datefinished");
    $notify_before_week = 7;
    $notify_before_month = 1;
    if($recurrence == 'yearly')
    {
        date_add($date,date_interval_create_from_date_string("$numrec year"));
        $dateend = date_format($date,'Y-m-d');
        $datenotify = date_create("$dateend");
        // NOTIFY BEFORE 1 WEEK

        date_sub($datenotify,date_interval_create_from_date_string("$notify_before_week day"));
        $datenotify_week = date_format($datenotify,'Y-m-d');
        $notification_message = "Hi ". $username .  " maybe you will perform this process again a week from now";
        $sql = "INSERT INTO recurrence_notification(processid,processname,process_from,notify_for,notification_message,datenotify,status) VALUES(?,?,?,?,?,?,?)";
        $st = $db->prepare($sql);
        $st->execute(array($processid,$processname,$process_from,$userid,$notification_message,$datenotify_week,$status));

        // NOTIFY BEFORE 1 MONTH

        $datenotify2 = date_create("$dateend");
        date_sub($datenotify2,date_interval_create_from_date_string("$notify_before_month month"));
        $datenotify_month = date_format($datenotify2,'Y-m-d');
        $notification_message = "Hi ". $username .  " maybe you will perform this process again a month from now";
        $sql2 = "INSERT INTO recurrence_notification(processid,processname,process_from,notify_for,notification_message,datenotify,status) VALUES(?,?,?,?,?,?,?)";
        $st2 = $db->prepare($sql2);
        $st2->execute(array($processid,$processname,$process_from,$userid,$notification_message,$datenotify_month,$status));


    }
    else
    {
        date_add($date,date_interval_create_from_date_string("$numrec month"));
        $dateend = date_format($date,'Y-m-d');
        $datenotify = date_create("$dateend");
        // NOTIFY BEFORE 1 WEEK
        date_sub($datenotify,date_interval_create_from_date_string("$notify_before_week day"));
        $dateend = date_format($date,'Y-m-d');
        $datenotify = date_create("$dateend");
        // NOTIFY BEFORE 1 WEEK

        date_sub($datenotify,date_interval_create_from_date_string("$notify_before_week day"));
        $datenotify_week = date_format($datenotify,'Y-m-d');
        $notification_message = "Hi ". $username .  " maybe you will perform this process again a week from now";
        $sql3 = "INSERT INTO recurrence_notification(processid,processname,process_from,notify_for,notification_message,datenotify,status) VALUES(?,?,?,?,?,?,?)";
        $st3 = $db->prepare($sql3);
        $st3->execute(array($processid,$processname,$process_from,$userid,$notification_message,$datenotify_week,$status));

    }

    $db=null;
}


/**
 * get notifications for agency
 */
function get_agency_notifications($userId){

    $database = DBConnect();
    $sql = "SELECT * FROM personal_notification WHERE euid = ? AND process_type = 'Agency' AND status = 'Unread'";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;

}

function get_personal_notifications($userId){

    $database = DBConnect();
    $sql = "SELECT * FROM personal_notification WHERE euid = ? AND process_type = 'Personal' AND status = 'Unread'";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;

}

/**
 * delete delete subscribed process
 * @param $userId
 * @param $processId
 */
function delete_subscribed_process($userId,$processId){

    $database = DBConnect();

    $sql = "DELETE FROM subscribedprocess WHERE euid = ? AND aprocessid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$processId));
    $database = null;

}


/**
 * delete sp requirements
 * @param $userId
 * @param $processId
 */
function delete_sp_requirements($userId,$processId){

    $database = DBConnect();
    $sql = "DELETE FROM sp_requirements WHERE user_id = ? AND processid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$processId));
    $database = null;

}

/**
 * delete sp steps
 */
function delete_sp_steps($userId,$processId){

    $database = DBConnect();
    $sql = "DELETE FROM sp_steps WHERE user_id = ? AND processid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$processId));
    $database = null;

}


/**
 * delete sp step required
 */
function sp_step_required($userId,$processId){

    $database = DBConnect();
    $sql = "DELETE FROM sp_stepsrequired WHERE user_id = ? AND processid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$processId));
    $database = null;
}

/**
 * delete sp steps repo
 */

function sp_steps_repo($userId,$processId){

    $database = DBConnect();
    $sql = "DELETE FROM sp_stepcopre WHERE user_id = ? AND processid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$processId));
    $database = null;

}


/**
 * download agency
 */
function download_agency_process_using_processId($processId){

    $database = DBConnect();
    $sql  = "SELECT ap.*,a.agencyname FROM agencyprocess ap JOIN agencies a ON ap.agencyid = a.agencyid WHERE ap.aprocessid = ? AND ap.status = 'Active'";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($processId));
    $result = $stmt->fetch();
    $database = null;
    return $result;
}

/**
 * change notification status
 */
function change_notification_status($userId,$aprocessId,$status){

    $database = DBConnect();
    $sql = "UPDATE personal_notification set status = ? WHERE euid = ? AND processid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($status,$userId,$aprocessId));
    $database = null;

}

/**
*recurrence
*/
function get_recurrence($userId){

    $database = DBConnect();
    $date = date('Y-m-d');
    $sql = "SELECT * FROM recurrence_notification WHERE notify_for = ? AND status = 'Unread' AND datenotify <= ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($userId,$date));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;
}

/**
 * fetch rec notification by nId
 */
function fetch_rec_notification_by_nId($notificationId){

    $database = DBConnect();
    $sql =  "SELECT * FROM recurrence_notification WHERE rec_notificationid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($notificationId));
    $result = $stmt->fetchAll();
    $database = null;
    return $result;

}

/**
 * change rec notification status
 */
function change_recurrence_notification_status($rec_notificationid,$status){

    $database = DBConnect();
    $sql  = "UPDATE recurrence_notification set status = ? WHERE rec_notificationid = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute(array($status,$rec_notificationid));
    $database = null;
}

function get_agencyid($processid)
{
    $database = DBConnect();
    $processid = $processid;
    $sql="SELECT agencyid FROM agencyprocess WHERE aprocessid = ?";
    $st=$database->prepare($sql);
    $st->execute(array($processid));
    $eu=$st->fetch();
    $db=null;
    return $eu;
}
function user($info)
{
    $database = DBConnect();
    $sql="SELECT * from enduser WHERE euid = ?";
    $st= $database->prepare($sql);
    $st->execute(array($info));
    $v = $st->fetch();
    $db=null;
    return $v;
}






































