<?php
namespace PB\E1V\Consolidation;

require_once "classes/ConsolidatedServiceUsageReportBase.php";
require_once "classes/dao/purls/PurlsDAOFactory.php";

class ConsolidatedServiceUsageReport extends ConsolidatedServiceUsageReportBase
{
   	
    /**
     * Additional headers to include in the generated CSV file
     *
     * @var array
	
    );
     */
	
    protected $additionalReportHeaders = array(
	"Nomor Polis",
	"Client Type",
	"Policy Holeder Name",
	"Life Assured",
	"Policy DOB",
	"Life Assured DOB",
	"Validate",
        "Confirm Buku Polis",
	"Confirm Data Polis",
	"Nilai NPS",
	"Feed Back",
	"Comment",
	"Issued Date",
	"Link Created",
	"Cycle Date"
);
     /**
     * Returns an array for data to use in the report for the additional fields
     *
     * @param string $userId           The unique ID for the reporting user
     * @param array  $visitsWithEvents The visits for the user
     *
	
     * @return array
     */
    protected function getAdditionalUserReportArray($userId, $visitsWithEvents)
    {
        $extraData = array(
	"Nomor Polis" => '',
	"Client Type" => '',
	"Policy Holder name" => '',
 	"Life Assured" => '',
	"Policy DOB" => '',
	"Life Assured DOB" => '',
	"Validate" => array(),
	"Confrim Buku Polis" => array(),
	"Confrim Data Polis" => array(),
	"Nilai NPS" => array(),
	"Feed Back" => array(),
	"Comment" => array(),
	"Issued Date" =>'',
	"Link Created" => '',
	"Cycle Date" => ''
        );
 

        $userPurlData = array();

        // Loop around the sessions for each user and retrieve the data we're interested in
        foreach ($visitsWithEvents as $visit) {

           // Get the PURL data for this visit if it has not already been loaded
           // IMPORTANT: this can cause significant performance slowdowns when generating the CSV file
           // for lots of users or visits. Use with care.
           if (isset($visit["uid"]) && !isset($userPurlData["uid"])) {
               $data = $this->getPurlData($visit["uid"]);	
		$extraData["Nomor Polis"] = $data["POLICY_NUMBER"];
		$extraData["Client Type"] = $data["CLIENT_TYPE"];
		$extraData["Policy Holder name"] = $data["POLICY_HOLDER_NAME"];
		$extraData["Life Assured"] = $data["LIFE_ASSURED"];
		$extraData["Policy DOB"] = $data["POLICY_HOLDER_DATE_OF_BIRTH"];
		$extraData["Life Assured DOB"] = $data["POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED"];
		$extraData["Issued Date"] = $data["ISSUED_DATE"];
		$extraData["Link Created"]= $data["LINK_CREATED"];
		$extraData["Cycle Date"]= $data["CYCLE_DATE"];
           }
           // TODO -- remove the above three lines if you do not need PURL data

           $data = json_decode($visit["data"], true);
           $visitData = $data["videoVisitData"];

           if (isset($visitData["VALIDATE"])) {
               $extraData["Validate"][] = $visitData["VALIDATE"];
           }
	   if (isset($visitData["CONFRIM_BUKU_POLIS"])) {
               $extraData["Confrim Buku Polis"][] = $visitData["CONFRIM_BUKU_POLIS"];
           }
	   if (isset($visitData["CONFRIM_DATA_POLIS"])) {
               $extraData["Confrim Data Polis"][] = $visitData["CONFRIM_DATA_POLIS"];
           }
	   if (isset($visitData["SURVEY"])) {
               $extraData["Nilai NPS"][] = $visitData["SURVEY"];
           }
	   if (isset($visitData["FEEDBACK"])) {
               $extraData["Feed Back"][] = $visitData["FEEDBACK"];
           }
	   if (isset($visitData["COMMENT"])) {
               $extraData["Comment"][] = $visitData["COMMENT"];
           }
           /* Array of all events for this visit:
           foreach ($visit["events"] as $event) {
              // TODO -- use event data
           }

           // Array of all session video views for this visit:
           foreach ($visit["session_video_views"] as $sessionVideoView) {
              // TODO -- use session video view data
           }*/
       }

       // Convert the array into a comma-separated string
//	$extraData["Nomor Polis"] = $extraData["Nomor Polis"];
//	$extraData["Nama"] = $extraData["Nama"];
//	$extraData["DOB"] = $extraData["DOB"];
//	$extraData["Alamat"] =  $extraData["Alamat"];
        $extraData["Validate"] = implode(", ", $extraData["Validate"]);
	$extraData["Confrim Buku Polis"] = implode(", ", $extraData["Confrim Buku Polis"]);
	$extraData["Confrim Data Polis"] = implode(", ", $extraData["Confrim Data Polis"]);
	$extraData["Nilai NPS"] = implode(", ", $extraData["Nilai NPS"]);
	$extraData["Feed Back"] = implode(", ", $extraData["Feed Back"]);
	$extraData["Comment"] = implode(", ", $extraData["Comment"]);

      
        return $extraData;
    }

    /**
     * Load the PURL data for a given UID
     * @param string $uid The UID for the visit
     * @return array|null
     */
    protected function getPurlData($uid)
    {
        $purl = \PurlsDAOFactory::getFactory()->load($uid);

        // Get the PURL JSON data from the database
        if (is_array($purl) && isset($purl['data'])) {
            // Convert the JSON data to a string array to make it easier to handle
            $data = json_decode($purl['data'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return null;
    }
}
?>
