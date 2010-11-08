<?php

class MCAPI {
    var $version = "1.2";
    var $errorMessage;
    var $errorCode;
    
    /**
     * Cache the information on the API location on the server
     */
    var $apiUrl;
    
    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300; 
    
    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;
    
    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $api_key;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $secure = false;
    
    /**
     * Connect to the MailChimp API for a given list. All MCAPI calls require login before functioning
     * 
     * @param string $username_or_apikey Your MailChimp login user name OR apikey - always required
     * @param string $password Your MailChimp login password - only required when username passed instead of API Key
     */
    function MCAPI($username_or_apikey, $password=null, $secure=false) {
        //do more "caching" of the uuid for those people that keep instantiating this...
        $this->secure = $secure;
        $this->apiUrl = parse_url("http://api.mailchimp.com/" . $this->version . "/?output=php");
        if ( isset($GLOBALS["mc_api_key"]) && $GLOBALS["mc_api_key"]!="" ){
            $this->api_key = $GLOBALS["mc_api_key"];
        } elseif( $username_or_apikey && !$password ){
            $this->api_key = $GLOBALS["mc_api_key"] = $username_or_apikey;
        }  else {
            $this->api_key = $this->callServer("login", array("username" => $username_or_apikey, "password" => $password));
            $GLOBALS["mc_api_key"] = $this->api_key;
        }
    }
    function setTimeout($seconds){
        if (is_int($seconds)){
            $this->timeout = $seconds;
            return true;
        }
    }
    function getTimeout(){
        return $this->timeout;
    }
    function useSecure($val){
        if ($val===true){
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }
    
    /**
     * Unschedule a campaign that is scheduled to be sent in the future
     *
     * @section Campaign  Related
     * @example mcapi_campaignUnschedule.php
     * @example xml-rpc_campaignUnschedule.php
     *
     * @param string $cid the id of the campaign to unschedule
     * @return boolean true on success
     */
    function campaignUnschedule($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignUnschedule", $params);
    }

    /**
     * Schedule a campaign to be sent in the future
     *
     * @section Campaign  Related
     * @example mcapi_campaignSchedule.php
     * @example xml-rpc_campaignSchedule.php
     *
     * @param string $cid the id of the campaign to schedule
     * @param string $schedule_time the time to schedule the campaign. For A/B Split "schedule" campaigns, the time for Group A - in YYYY-MM-DD HH:II:SS format in <strong>GMT</strong>
     * @param string $schedule_time_b optional -the time to schedule Group B of an A/B Split "schedule" campaign - in YYYY-MM-DD HH:II:SS format in <strong>GMT</strong>
     * @return boolean true on success
     */
    function campaignSchedule($cid, $schedule_time, $schedule_time_b=NULL) {
        $params = array();
        $params["cid"] = $cid;
        $params["schedule_time"] = $schedule_time;
        $params["schedule_time_b"] = $schedule_time_b;
        return $this->callServer("campaignSchedule", $params);
    }

    /**
     * Resume sending an AutoResponder or RSS campaign
     *
     * @section Campaign  Related
     *
     * @param string $cid the id of the campaign to pause
     * @return boolean true on success
     */
    function campaignResume($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignResume", $params);
    }

    /**
     * Pause an AutoResponder orRSS campaign from sending
     *
     * @section Campaign  Related
     *
     * @param string $cid the id of the campaign to pause
     * @return boolean true on success
     */
    function campaignPause($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignPause", $params);
    }

    /**
     * Send a given campaign immediately
     *
     * @section Campaign  Related
     *
     * @example mcapi_campaignSendNow.php
     * @example xml-rpc_campaignSendNow.php
     *
     * @param string $cid the id of the campaign to resume
     * @return boolean true on success
     */
    function campaignSendNow($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignSendNow", $params);
    }

    /**
     * Send a test of this campaign to the provided email address
     *
     * @section Campaign  Related
     *
     * @example mcapi_campaignSendTest.php
     * @example xml-rpc_campaignSendTest.php
     *
     * @param string $cid the id of the campaign to test
     * @param array $test_emails an array of email address to receive the test message
     * @param string $send_type optional by default (null) both formats are sent - "html" or "text" send just that format
     * @return boolean true on success
     */
    function campaignSendTest($cid, $test_emails=array (
), $send_type=NULL) {
        $params = array();
        $params["cid"] = $cid;
        $params["test_emails"] = $test_emails;
        $params["send_type"] = $send_type;
        return $this->callServer("campaignSendTest", $params);
    }

    /**
     * Retrieve all templates defined for your user account
     *
     * @section Campaign  Related
     * @example mcapi_campaignTemplates.php
     * @example xml-rpc_campaignTemplates.php
     *
     * @return array An array of structs, one for each template (see Returned Fields for details)
     * @returnf integer id Id of the template
     * @returnf string name Name of the template
     * @returnf string layout Layout of the template - "basic", "left_column", "right_column", or "postcard"
     * @returnf array sections associative array of editable sections in the template that can accept custom HTML when sending a campaign
     */
    function campaignTemplates() {
        $params = array();
        return $this->callServer("campaignTemplates", $params);
    }

    /**
     * Allows one to test their segmentation rules before creating a campaign using them
     *
     * @section Campaign  Related
     * @example mcapi_campaignSegmentTest.php
     * @example xml-rpc_campaignSegmentTest.php
     *
     * @param string $list_id the list to test segmentation on - get lists using lists()
     * @param array $options with 2 keys:  
             string "match" controls whether to use AND or OR when applying your options - expects "<strong>any</strong>" (for OR) or "<strong>all</strong>" (for AND)
             array "conditions" - up to 10 different criteria to apply while segmenting. Each criteria row must contain 3 keys - "<strong>field</strong>", "<strong>op</strong>", and "<strong>value</strong>" - and possibly a fourth, "<strong>extra</strong>", based on these definitions:
    
            Field = "<strong>date</strong>" : Select based on various dates we track
                Valid Op(eration): <strong>eq</strong> (is) / <strong>gt</strong> (after) / <strong>lt</strong> (before)
                Valid Values: 
                string last_campaign_sent  uses the date of the last campaign sent
                string campaign_id - uses the send date of the campaign that carriers the Id submitted - see campaigns()
                string YYYY-MM-DD - any date in the form of YYYY-MM-DD - <em>note:</em> anything that appears to start with YYYY will be treated as a date
                          
            Field = "<strong>interests</strong>":
                Valid Op(erations): <strong>one</strong> / <strong>none</strong> / <strong>all</strong> 
                Valid Values: a comma delimited of interest groups for the list - see listInterestGroups()    
        
            Field = "<strong>aim</strong>"
                Valid Op(erations): <strong>open</strong> / <strong>noopen</strong> / <strong>click</strong> / <strong>noclick</strong>
                Valid Values: "<strong>any</strong>" or a valid AIM-enabled Campaign that has been sent
    
            Field = "<strong>rating</strong>" : allows matching based on list member ratings
                Valid Op(erations):  <strong>eq</strong> (=) / <strong>ne</strong> (!=) / <strong>gt</strong> (&gt;) / <strong>lt</strong> (&lt;)
                Valid Values: a number between 0 and 5
    
            Field = "<strong>ecomm_prod</strong>" or "<strong>ecomm_prod</strong>": allows matching product and category names from purchases
                Valid Op(erations): 
                 <strong>eq</strong> (=) / <strong>ne</strong> (!=) / <strong>gt</strong> (&gt;) / <strong>lt</strong> (&lt;) / <strong>like</strong> (like '%blah%') / <strong>nlike</strong> (not like '%blah%') / <strong>starts</strong> (like 'blah%') / <strong>ends</strong> (like '%blah')
                Valid Values: any string
    
            Field = "<strong>ecomm_spent_one</strong>" or "<strong>ecomm_spent_all</strong>" : allows matching purchase amounts on a single order or all orders
                Valid Op(erations): <strong>gt</strong> (&gt;) / <strong>lt</strong> (&lt;)
                Valid Values: a number
    
            Field = "<strong>ecomm_date</strong>" : allow matching based on order dates
                Valid Op(eration): <strong>eq</strong> (is) / <strong>gt</strong> (after) / <strong>lt</strong> (before)
                Valid Values: 
                string YYYY-MM-DD - any date in the form of YYYY-MM-DD
    
            Field = An <strong>Address</strong> Merge Var. Use <strong>Merge0-Merge30</strong> or the <strong>Custom Tag</strong> you've setup for your merge field - see listMergeVars(). Note, Address fields can still be used with the default operations below - this section is broken out solely to highlight the differences in using the geolocation routines.
                Valid Op(erations): <strong>geoin</strong>
                Valid Values: The number of miles an address should be within
                Extra Value: The Zip Code to be used as the center point
        
            Default Field = A Merge Var. Use <strong>Merge0-Merge30</strong> or the <strong>Custom Tag</strong> you've setup for your merge field - see listMergeVars()
                Valid Op(erations): 
                 <strong>eq</strong> (=) / <strong>ne</strong> (!=) / <strong>gt</strong> (&gt;) / <strong>lt</strong> (&lt;) / <strong>like</strong> (like '%blah%') / <strong>nlike</strong> (not like '%blah%') / <strong>starts</strong> (like 'blah%') / <strong>ends</strong> (like '%blah')
                Valid Values: any string
     * @return integer total The total number of subscribers matching your segmentation options
     */
    function campaignSegmentTest($list_id, $options) {
        $params = array();
        $params["list_id"] = $list_id;
        $params["options"] = $options;
        return $this->callServer("campaignSegmentTest", $params);
    }

    /**
     * Create a new draft campaign to send
     *
     * @section Campaign  Related
     * @example mcapi_campaignCreate.php
     * @example xml-rpc_campaignCreate.php
     * @example xml-rpc_campaignCreateABSplit.php
     * @example xml-rpc_campaignCreateRss.php
     *
     * @param string $type the Campaign Type to create - one of "regular", "plaintext", "absplit", "rss", "trans", "auto"
     * @param array $options a hash of the standard options for this campaign :
            string list_id the list to send this campaign to- get lists using lists()
            string subject the subject line for your campaign message
            string from_email the From: email address for your campaign message
            string from_name the From: name for your campaign message (not an email address)
            string to_email the To: name recipients will see (not email address)
            integer template_id optional - use this template to generate the HTML content of the campaign
            integer folder_id optional - automatically file the new campaign in the folder_id passed
            array tracking optional - set which recipient actions will be tracked, as a struct of boolean values with the following keys: "opens", "html_clicks", and "text_clicks".  By default, opens and HTML clicks will be tracked.
            string title optional - an internal name to use for this campaign.  By default, the campaign subject will be used.
            boolean authenticate optional - set to true to enable SenderID, DomainKeys, and DKIM authentication, defaults to false.
            array analytics optional - if provided, use a struct with "service type" as a key and the "service tag" as a value. For Google, this should be "google"=>"your_google_analytics_key_here". Note that only "google" is currently supported - a Google Analytics tags will be added to all links in the campaign with this string attached. Others may be added in the future
            boolean auto_footer optional Whether or not we should auto-generate the footer for your content. Mostly useful for content from URLs or Imports
            boolean inline_css optional Whether or not css should be automatically inlined when this campaign is sent, defaults to false.
            boolean generate_text optional Whether of not to auto-generate your Text content from the HTML content. Note that this will be ignored if the Text part of the content passed is not empty, defaults to false.
    
    * @param array $content the content for this campaign - use a struct with the following keys: 
                "html" for pasted HTML content
                "text" for the plain-text version
                "url" to have us pull in content from a URL. Note, this will override any other content options - for lists with Email Format options, you'll need to turn on generate_text as well
                "archive" to send a Base64 encoded archive file for us to import all media from. Note, this will override any other content options - for lists with Email Format options, you'll need to turn on generate_text as well
                "archive_type" optional - only necessary for the "archive" option. Supported formats are: zip, tar.gz, tar.bz2, tar, tgz, tbz . If not included, we will default to zip
                
                
                If you chose a template instead of pasting in your HTML content, then use "html_" followed by the template sections as keys - for example, use a key of "html_MAIN" to fill in the "MAIN" section of a template. Supported template sections include: "html_HEADER", "html_MAIN", "html_SIDECOLUMN", and "html_FOOTER"
    * @param array $segment_opts optional - if you wish to do Segmentation with this campaign this array should contain: see campaignSegmentTest(). It's suggested that you test your options against campaignSegmentTest(). Also, "trans" campaigns <strong>do not</strong> support segmentation.
    * @param array $type_opts optional - 
            For RSS Campaigns this, array should contain:
                string url the URL to pull RSS content from - it will be verified and must exist
                string schedule optional one of "daily", "weekly", "monthly" - defaults to "daily"
                string schedule_hour optional an hour between 0 and 24 - default to 4 (4am <em>local time</em>) - applies to all schedule types
                string schedule_weekday optional for "weekly" only, a number specifying the day of the week to send: 0 (Sunday) - 6 (Saturday) - defaults to 1 (Monday)
                string schedule_monthday optional for "monthly" only, a number specifying the day of the month to send (1 - 28) or "last" for the last day of a given month. Defaults to the 1st day of the month
             
            For A/B Split campaigns, this array should contain:
                string split_test The values to segment based on. Currently, one of: "subject", "from_name", "schedule". NOTE, for "schedule", you will need to call campaignSchedule() separately!
                string pick_winner How the winner will be picked, one of: "opens" (by the open_rate), "clicks" (by the click rate), "manual" (you pick manually)
                integer wait_units optional the default time unit to wait before auto-selecting a winner - use "3600" for hours, "86400" for days. Defaults to 86400.
                integer wait_time optional the number of units to wait before auto-selecting a winner - defaults to 1, so if not set, a winner will be selected after 1 Day.
                integer split_size optional this is a percentage of what size the Campaign's List plus any segmentation options results in. "schedule" type forces 50%, all others default to 10%
                string from_name_a optional sort of, required when split_test is "from_name"
                string from_name_b optional sort of, required when split_test is "from_name"
                string from_email_a optional sort of, required when split_test is "from_name"
                string from_email_b optional sort of, required when split_test is "from_name"
                string subject_a optional sort of, required when split_test is "subject"
                string subject_b optional sort of, required when split_test is "subject"
                
            For AutoResponder campaigns, this array should contain:
                string offset-units one of "day", "week", "month", "year" - required
                string offset-time the number of units, must be a number greater than 0 - required
                string offset-dir either "before" or "after"
                string event optional "signup" (default) to base this on double-optin signup, "date" or "annual" to base this on merge field in the list
                string event-datemerge optional sort of, this is required if the event is "date" or "annual"
    
     *
     * @return string the ID for the created campaign
     */
    function campaignCreate($type, $options, $content, $segment_opts=NULL, $type_opts=NULL) {
        $params = array();
        $params["type"] = $type;
        $params["options"] = $options;
        $params["content"] = $content;
        $params["segment_opts"] = $segment_opts;
        $params["type_opts"] = $type_opts;
        return $this->callServer("campaignCreate", $params);
    }

    /** Update just about any setting for a campaign that has <em>not</em> been sent. See campaignCreate() for details
     *
     *  Caveats:<br/><ul>
     *        <li>If you set list_id, all segmentation options will be deleted and must be re-added.</li>
     *        <li>If you set template_id, you need to follow that up by setting it's 'content'</li>
     *        <li>If you set segment_opts, you should have tested your options against campaignSegmentTest() as campaignUpdate() will not allow you to set a segment that includes no members.</li></ul>
     * @section Campaign  Related
     *
     * @example mcapi_campaignUpdate.php
     * @example mcapi_campaignUpdateAB.php
     * @example xml-rpc_campaignUpdate.php
     * @example xml-rpc_campaignUpdateAB.php
     *
     * @param string $cid the Campaign Id to update
     * @param string $name the parameter name ( see campaignCreate() )
     * @param mixed  $value an appropriate value for the parameter ( see campaignCreate() )
     * @return boolean true if the update succeeds, otherwise an error will be thrown
     */
    function campaignUpdate($cid, $name, $value) {
        $params = array();
        $params["cid"] = $cid;
        $params["name"] = $name;
        $params["value"] = $value;
        return $this->callServer("campaignUpdate", $params);
    }

    /** Replicate a campaign.
    *
    * @section Campaign  Related
    *
    * @example mcapi_campaignReplicate.php
    *
    * @param string $cid the Campaign Id to replicate
    * @return string the id of the replicated Campaign created, otherwise an error will be thrown
    */
    function campaignReplicate($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignReplicate", $params);
    }

    /** Delete a campaign. Seriously, "poof, gone!" - be careful!
    *
    * @section Campaign  Related
    *
    * @example mcapi_campaignDelete.php
    *
    * @param string $cid the Campaign Id to delete
    * @return boolean true if the delete succeeds, otherwise an error will be thrown
    */
    function campaignDelete($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignDelete", $params);
    }

    /**
     * Get the list of campaigns and their details matching the specified filters
     *
     * @section Campaign  Related
     * @example mcapi_campaigns.php
     * @example xml-rpc_campaigns.php
     *
     * @param array $filters a hash of filters to apply to this query - all are optional:
            string  campaign_id optional - return a single campaign using a know campaign_id
            string  list_id optional - the list to send this campaign to- get lists using lists()
            integer folder_id optional - only show campaigns from this folder id - get folders using campaignFolders()
            string  type optional - return campaigns of a specific type - one of "regular", "plaintext", "absplit", "rss", "trans", "auto"
            string  from_name optional - only show campaigns that have this "From Name"
            string  from_email optional - only show campaigns that have this "Reply-to Email"
            string  title optional - only show campaigns that have this title
            string  subject optional - only show campaigns that have this subject
            string  sendtime_start optional - only show campaigns that have been sent since this date/time (in GMT) - format is YYYY-MM-DD HH:mm:ss (24hr)
            string  sendtime_end optional - only show campaigns that have been sent before this date/time (in GMT) - format is YYYY-MM-DD HH:mm:ss (24hr)
            boolean exact optional - flag for whether to filter on exact values when filtering, or search within content for filter values - defaults to true
     * @param integer $start optional - control paging of campaigns, start results at this campaign #, defaults to 1st page of data  (page 0)
     * @param integer $limit optional - control paging of campaigns, number of campaigns to return with each call, defaults to 25 (max=1000)
     * @return array list of campaigns and their associated information (see Returned Fields for description)
     * @returnf string id Campaign Id (used for all other campaign functions)
     * @returnf integer web_id The Campaign id used in our web app, allows you to create a link directly to it
     * @returnf string title Title of the campaign
     * @returnf string type The type of campaign this is (regular,plaintext,absplit,rss,inspection,trans,auto)
     * @returnf date create_time Creation time for the campaign
     * @returnf date send_time Send time for the campaign
     * @returnf integer emails_sent Number of emails email was sent to
     * @returnf string status Status of the given campaign (save,paused,schedule,sending,sent)
     * @returnf string from_name From name of the given campaign
     * @returnf string from_email Reply-to email of the given campaign
     * @returnf string subject Subject of the given campaign
     * @returnf string to_email Custom "To:" email string using merge variables
     * @returnf string archive_url Archive link for the given campaign
     * @returnf boolean inline_css Whether or not the campaigns content auto-css-lined
     * @returnf string analytics Either "google" if enabled or "N" if disabled
     * @returnf string analytcs_tag The name/tag the campaign's links were tagged with if analytics were enabled.
     * @returnf boolean track_clicks_text Whether or not links in the text version of the campaign were tracked
     * @returnf boolean track_clicks_html Whether or not links in the html version of the campaign were tracked
     * @returnf boolean track_opens Whether or not opens for the campaign were tracked
     * @returnf array segment_opts the segment used for the campaign - can be passed to campaignSegmentTest() or campaignCreate()
     */
    function campaigns($filters=array (
), $start=0, $limit=25) {
        $params = array();
        $params["filters"] = $filters;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaigns", $params);
    }

    /**
     * List all the folders for a user account
     *
     * @section Campaign  Related
     * @example mcapi_campaignFolders.php
     * @example xml-rpc_campaignFolders.php
     *
     * @return array Array of folder structs (see Returned Fields for details)
     * @returnf integer folder_id Folder Id for the given folder, this can be used in the campaigns() function to filter on.
     * @returnf string name Name of the given folder
     */
    function campaignFolders() {
        $params = array();
        return $this->callServer("campaignFolders", $params);
    }

    /**
     * Given a list and a campaign, get all the relevant campaign statistics (opens, bounces, clicks, etc.)
     *
     * @section Campaign  Stats
     *
     * @example mcapi_campaignStats.php
     * @example xml-rpc_campaignStats.php
     *
     * @param string $cid the campaign id to pull stats for (can be gathered using campaigns())
     * @return array struct of the statistics for this campaign
     * @returnf integer syntax_errors Number of email addresses in campaign that had syntactical errors.
     * @returnf integer hard_bounces Number of email addresses in campaign that hard bounced.
     * @returnf integer soft_bounces Number of email addresses in campaign that soft bounced.
     * @returnf integer unsubscribes Number of email addresses in campaign that unsubscribed.
     * @returnf integer abuse_reports Number of email addresses in campaign that reported campaign for abuse.
     * @returnf integer forwards Number of times email was forwarded to a friend.
     * @returnf integer forwards_opens Number of times a forwarded email was opened.
     * @returnf integer opens Number of times the campaign was opened.
     * @returnf date last_open Date of the last time the email was opened.
     * @returnf integer unique_opens Number of people who opened the campaign.
     * @returnf integer clicks Number of times a link in the campaign was clicked.
     * @returnf integer unique_clicks Number of unique recipient/click pairs for the campaign.
     * @returnf date last_click Date of the last time a link in the email was clicked.
     * @returnf integer users_who_clicked Number of unique recipients who clicked on a link in the campaign.
     * @returnf integer emails_sent Number of email addresses campaign was sent to.
     */
    function campaignStats($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignStats", $params);
    }

    /**
     * Get an array of the urls being tracked, and their click counts for a given campaign
     *
     * @section Campaign  Stats
     *
     * @example mcapi_campaignClickStats.php
     * @example xml-rpc_campaignClickStats.php
     *
     * @param string $cid the campaign id to pull stats for (can be gathered using campaigns())
     * @return struct urls will be keys and contain their associated statistics:
     * @returnf integer clicks Number of times the specific link was clicked
     * @returnf integer unique Number of unique people who clicked on the specific link
     */
    function campaignClickStats($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignClickStats", $params);
    }

    /**
     * Get the top 5 performing email domains for this campaign. Users want more than 5 should use campaign campaignEmailStatsAIM()
     * or campaignEmailStatsAIMAll() and generate any additional stats they require.
     * 
     * @section Campaign  Stats
     *
     * @example mcapi_campaignEmailDomainPerformance.php
     *
     * @param string $cid the campaign id to pull email domain performance for (can be gathered using campaigns())
     * @return array domains email domains and their associated stats
     * @returnf string domain Domain name or special "Other" to roll-up stats past 5 domains
     * @returnf integer total_sent Total Email across all domains - this will be the same in every row
     * @returnf integer emails Number of emails sent to this domain
     * @returnf integer bounces Number of bounces
     * @returnf integer opens Number of opens
     * @returnf integer clicks Number of clicks
     * @returnf integer unsubs Number of unsubs
     * @returnf integer delivered Number of deliveries
     * @returnf integer emails_pct Percentage of emails that went to this domain (whole number)
     * @returnf integer bounces_pct Percentage of bounces from this domain (whole number)
     * @returnf integer opens_pct Percentage of opens from this domain (whole number)
     * @returnf integer clicks_pct Percentage of clicks from this domain (whole number)
     * @returnf integer unsubs_pct Percentage of unsubs from this domain (whole number) 
     */
    function campaignEmailDomainPerformance($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignEmailDomainPerformance", $params);
    }

    /**
     * Get all email addresses with Hard Bounces for a given campaign
     *
     * @section Campaign  Stats
     *
     * @param string $cid the campaign id to pull bounces for (can be gathered using campaigns())
     * @param integer    $start optional for large data sets, the page number to start at - defaults to 1st page of data (page 0)
     * @param integer    $limit optional for large data sets, the number of results to return - defaults to 1000, upper limit set at 15000
     * @return array Arrays of email addresses with Hard Bounces
     */
    function campaignHardBounces($cid, $start=0, $limit=1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignHardBounces", $params);
    }

    /**
     * Get all email addresses with Soft Bounces for a given campaign
     *
     * @section Campaign  Stats
     *
     * @param string $cid the campaign id to pull bounces for (can be gathered using campaigns())
     * @param integer    $start optional for large data sets, the page number to start at - defaults to 1st page of data (page 0)
     * @param integer    $limit optional for large data sets, the number of results to return - defaults to 1000, upper limit set at 15000
     * @return array Arrays of email addresses with Soft Bounces
     */
    function campaignSoftBounces($cid, $start=0, $limit=1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignSoftBounces", $params);
    }

    /**
     * Get all unsubscribed email addresses for a given campaign
     *
     * @section Campaign  Stats
     *
     * @param string $cid the campaign id to pull bounces for (can be gathered using campaigns())
     * @param integer    $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer    $limit optional for large data sets, the number of results to return - defaults to 1000, upper limit set at 15000
     * @return array list of email addresses that unsubscribed from this campaign
     */
    function campaignUnsubscribes($cid, $start=0, $limit=1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignUnsubscribes", $params);
    }

    /**
     * Get all email addresses that complained about a given campaign
     *
     * @section Campaign  Stats
     *
     * @example mcapi_campaignAbuseReports.php
     *
     * @param string $cid the campaign id to pull abuse reports for (can be gathered using campaigns())
     * @param integer $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer $limit optional for large data sets, the number of results to return - defaults to 500, upper limit set at 1000
     * @param string $since optional pull only messages since this time - use YYYY-MM-DD HH:II:SS format in <strong>GMT</strong>
     * @return array reports the abuse reports for this campaign
     * @returnf string date date/time the abuse report was received and processed
     * @returnf string email the email address that reported abuse
     * @returnf string type an internal type generally specifying the orginating mail provider - may not be useful outside of filling report views
     */
    function campaignAbuseReports($cid, $since=NULL, $start=0, $limit=500) {
        $params = array();
        $params["cid"] = $cid;
        $params["since"] = $since;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignAbuseReports", $params);
    }

    /**
     * Retrieve the text presented in our app for how a campaign performed and any advice we may have for you - best
     * suited for display in customized reports pages. Note: some messages will contain HTML - clean tags as necessary
     *
     * @section Campaign  Stats
     *
     * @example mcapi_campaignAdvice.php
     *
     * @param string $cid the campaign id to pull advice text for (can be gathered using campaigns())
     * @return array advice on the campaign's performance
     * @returnf msg the advice message
     * @returnf type the "type" of the message. one of: negative, positive, or neutral
     */
    function campaignAdvice($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignAdvice", $params);
    }

    /**
     * Retrieve the Google Analytics data we've collected for this campaign. Note, requires Google Analytics Add-on to be installed and configured.
     *
     * @section Campaign  Stats
     *
     * @example mcapi_campaignAnalytics.php
     *
     * @param string $cid the campaign id to pull bounces for (can be gathered using campaigns())
     * @return array analytics we've collected for the passed campaign.
     * @returnf integer visits number of visits
     * @returnf integer pages number of page views
     * @returnf integer new_visits new visits recorded
     * @returnf integer bounces vistors who "bounced" from your site
     * @returnf double time_on_site
     * @returnf integer goal_conversions number of goals converted
     * @returnf double goal_value value of conversion in dollars
     * @returnf double revenue revenue generated by campaign
     * @returnf integer transactions number of transactions tracked
     * @returnf integer ecomm_conversions number Ecommerce transactions tracked
     * @returnf array goals an array containing goal names and number of conversions
     */
    function campaignAnalytics($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignAnalytics", $params);
    }

    /**
     * Retrieve the full bounce messages for the given campaign. Note that this can return very large amounts
     * of data depending on how large the campaign was and how much cruft the bounce provider returned. Also,
     * message over 30 days old are subject to being removed
     * 
     * @section Campaign  Stats
     *
     * @example mcapi_campaignBounceMessages.php
     *
     * @param string $cid the campaign id to pull bounces for (can be gathered using campaigns())
     * @param integer $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer $limit optional for large data sets, the number of results to return - defaults to 25, upper limit set at 50
     * @param string $since optional pull only messages since this time - use YYYY-MM-DD format in <strong>GMT</strong> (we only store the date, not the time)
     * @return array bounces the full bounce messages for this campaign
     * @returnf string date date/time the bounce was received and processed
     * @returnf string email the email address that bounced
     * @returnf string message the entire bounce message received
     */
    function campaignBounceMessages($cid, $start=0, $limit=25, $since=NULL) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        $params["since"] = $since;
        return $this->callServer("campaignBounceMessages", $params);
    }

    /**
     * Retrieve the Ecommerce Orders tracked by campaignEcommAddOrder()
     * 
     * @section Campaign  Stats
     *
     * @param string $cid the campaign id to pull bounces for (can be gathered using campaigns())
     * @param integer $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer $limit optional for large data sets, the number of results to return - defaults to 100, upper limit set at 500
     * @param string $since optional pull only messages since this time - use YYYY-MM-DD HH:II:SS format in <strong>GMT</strong>
     * @return array orders the orders and their details that we've collected for this campaign
     * @returnf store_id string the store id generated by the plugin used to uniquely identify a store
     * @returnf store_name string the store name collected by the plugin - often the domain name
     * @returnf order_id string the internal order id the store tracked this order by
     * @returnf email string the email address that received this campaign and is associated with this order
     * @returnf order_total double the order total
     * @returnf tax_total double the total tax for the order (if collected)
     * @returnf ship_total double the shipping total for the order (if collected)
     * @returnf order_date string the date the order was tracked - from the store if possible, otherwise the GMT time we recieved it
     * @returnf lines array containing detail of the order - product, category, quantity, item cost
     */
    function campaignEcommOrders($cid, $start=0, $limit=100, $since=NULL) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        $params["since"] = $since;
        return $this->callServer("campaignEcommOrders", $params);
    }

    /**
     * Get the URL to a customized VIP Report for the specified campaign and optionally send an email to someone with links to it. Note subsequent calls will overwrite anything already set for the same campign (eg, the password)
     *
     * @section Campaign  Related
     *
     * @param string $cid the campaign id to share a report for (can be gathered using campaigns())
     * @param array  $opts optional various parameters which can be used to configure the shared report
            string  header_type optional - "text" or "image', defaults to "text'
            string  header_data optional - if "header_type" is text, the text to display. if "header_type" is "image" a valid URL to an image file. Note that images will be resized to be no more than 500x150. Defaults to the Accounts Company Name.
            bool    secure optional - whether to require a password for the shared report. defaults to "true"
            string  password optional - if secure is true and a password is not included, we will generate one. It is always returned.
            string  to_email optional - optional, email address to share the report with - no value means an email will not be sent
            array   theme  optional - an array containing either 3 or 6 character color code values for: "bg_color", "header_color", "current_tab", "current_tab_text", "normal_tab", "normal_tab_text", "hover_tab", "hover_tab_text"
            string  css_url    optional - a link to an external CSS file to be included after our default CSS (http://vip-reports.net/css/vip.css) <strong>only if</strong> loaded in an IFRAME - max 255 characters
     * @return struct Struct containing details for the shared report
     * @returnf string title The Title of the Campaign being shared
     * @returnf string url The URL to the shared report
     * @returnf string secure_url The URL to the shared report, including the password (good for loading in an IFRAME). For non-secure reports, this will not be returned
     * @returnf string password If secured, the password for the report, otherwise this field will not be returned
     */
    function campaignShareReport($cid, $opts=array (
)) {
        $params = array();
        $params["cid"] = $cid;
        $params["opts"] = $opts;
        return $this->callServer("campaignShareReport", $params);
    }

    /**
     * Get the content (both html and text) for a campaign either as it would appear in the campaign archive or as the raw, original content
     *
     * @section Campaign  Related
     *
     * @param string $cid the campaign id to get content for (can be gathered using campaigns())
     * @param bool   $for_archive optional controls whether we return the Archive version (true) or the Raw version (false), defaults to true
     * @return struct Struct containing all content for the campaign (see Returned Fields for details
     * @returnf string html The HTML content used for the campgain with merge tags intact
     * @returnf string text The Text content used for the campgain with merge tags intact
     */
    function campaignContent($cid, $for_archive=true) {
        $params = array();
        $params["cid"] = $cid;
        $params["for_archive"] = $for_archive;
        return $this->callServer("campaignContent", $params);
    }

    /**
     * Retrieve the list of email addresses that opened a given campaign with how many times they opened - note: this AIM function is free and does
     * not actually require the AIM module to be installed
     *
     * @section Campaign AIM
     *
     * @param string $cid the campaign id to get opens for (can be gathered using campaigns())
     * @param integer    $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer    $limit optional for large data sets, the number of results to return - defaults to 1000, upper limit set at 15000
     * @return array Array of structs containing email addresses and open counts
     * @returnf string email Email address that opened the campaign
     * @returnf integer open_count Total number of times the campaign was opened by this email address
     */
    function campaignOpenedAIM($cid, $start=0, $limit=1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignOpenedAIM", $params);
    }

    /**
     * Retrieve the list of email addresses that did not open a given campaign
     *
     * @section Campaign AIM
     *
     * @param string $cid the campaign id to get no opens for (can be gathered using campaigns())
     * @param integer    $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer    $limit optional for large data sets, the number of results to return - defaults to 1000, upper limit set at 15000
     * @return array list of email addresses that did not open a campaign
     */
    function campaignNotOpenedAIM($cid, $start=0, $limit=1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignNotOpenedAIM", $params);
    }

    /**
     * Return the list of email addresses that clicked on a given url, and how many times they clicked
     *
     * @section Campaign AIM
     *
     * @param string $cid the campaign id to get click stats for (can be gathered using campaigns())
     * @param string $url the URL of the link that was clicked on
     * @param integer    $start optional for large data sets, the page number to start at - defaults to 1st page of data (page 0)
     * @param integer    $limit optional for large data sets, the number of results to return - defaults to 1000, upper limit set at 15000
     * @return array Array of structs containing email addresses and click counts
     * @returnf string email Email address that opened the campaign
     * @returnf integer clicks Total number of times the URL was clicked on by this email address
     */
    function campaignClickDetailAIM($cid, $url, $start=0, $limit=1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["url"] = $url;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignClickDetailAIM", $params);
    }

    /**
     * Given a campaign and email address, return the entire click and open history with timestamps, ordered by time
     *
     * @section Campaign AIM
     *
     * @param string $cid the campaign id to get stats for (can be gathered using campaigns())
     * @param string $email_address the email address to check
     * @return array Array of structs containing the actions (opens and clicks) that the email took, with timestamps
     * @returnf string action The action taken (open or click)
     * @returnf date timestamp Time the action occurred
     * @returnf string url For clicks, the URL that was clicked
     */
    function campaignEmailStatsAIM($cid, $email_address) {
        $params = array();
        $params["cid"] = $cid;
        $params["email_address"] = $email_address;
        return $this->callServer("campaignEmailStatsAIM", $params);
    }

    /**
     * Given a campaign and correct paging limits, return the entire click and open history with timestamps, ordered by time, 
     * for every user a campaign was delivered to.
     *
     * @section Campaign AIM
     * @example mcapi_campaignEmailStatsAIMAll.php
     *
     * @param string $cid the campaign id to get stats for (can be gathered using campaigns())
     * @param integer $start optional for large data sets, the page number to start at - defaults to 1st page of data (page 0)
     * @param integer $limit optional for large data sets, the number of results to return - defaults to 100, upper limit set at 1000
     * @return array Array of structs containing actions  (opens and clicks) for each email, with timestamps
     * @returnf string action The action taken (open or click)
     * @returnf date timestamp Time the action occurred
     * @returnf string url For clicks, the URL that was clicked
     */
    function campaignEmailStatsAIMAll($cid, $start=0, $limit=100) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignEmailStatsAIMAll", $params);
    }

    /**
     * Attach Ecommerce Order Information to a Campaign. This will generall be used by ecommerce package plugins 
     * <a href="/plugins/ecomm360.phtml">that we provide</a> or by 3rd part system developers.
     * @section Campaign  Related
     *
     * @param array $order an array of information pertaining to the order that has completed. Use the following keys:
                string id the Order Id
                string campaign_id the Campaign Id to track this order with (see the "mc_cid" query string variable a campaign passes)
                string email_id the Email Id of the subscriber we should attach this order to (see the "mc_eid" query string variable a campaign passes)
                double total The Order Total (ie, the full amount the customer ends up paying)
                double shipping optional the total paid for Shipping Fees
                double tax optional the total tax paid
                string store_id a unique id for the store sending the order in
                string store_name optional a "nice" name for the store - typically the base web address (ie, "store.mailchimp.com"). We will automatically update this if it changes (based on store_id)
                string plugin_id the MailChimp assigned Plugin Id. Get yours by <a href="/api/register.php">registering here</a>
                array items the individual line items for an order using these keys:
                <div style="padding-left:30px"><table><tr><td colspan=*>
                    integer line_num optional the line number of the item on the order. We will generate these if they are not passed
                    integer product_id the store's internal Id for the product. Lines that do no contain this will be skipped 
                    string product_name the product name for the product_id associated with this item. We will auto update these as they change (based on product_id)
                    integer category_id the store's internal Id for the (main) category associated with this product. Our testing has found this to be a "best guess" scenario
                    string category_name the category name for the category_id this product is in. Our testing has found this to be a "best guess" scenario. Our plugins walk the category heirarchy up and send "Root - SubCat1 - SubCat4", etc.
                    double qty the quantity of the item ordered
                    double cost the cost of a single item (ie, not the extended cost of the line)
                </td></tr></table></div>
     * @return bool true if the data is saved, otherwise an error is thrown.
     */
    function campaignEcommAddOrder($order) {
        $params = array();
        $params["order"] = $order;
        return $this->callServer("campaignEcommAddOrder", $params);
    }

    /**
     * Retrieve all of the lists defined for your user account
     *
     * @section List Related
     * @example mcapi_lists.php
     * @example xml-rpc_lists.php
     *
     * @return array list of your Lists and their associated information (see Returned Fields for description)
     * @returnf string id The list id for this list. This will be used for all other list management functions.
     * @returnf integer web_id The list id used in our web app, allows you to create a link directly to it
     * @returnf string name The name of the list.
     * @returnf date date_created The date that this list was created.
     * @returnf integer member_count The number of active members in the given list.
     * @returnf integer unsubscribe_count The number of members who have unsubscribed from the given list.
     * @returnf integer cleaned_count The number of members cleaned from the given list.
     * @returnf boolean email_type_option Whether or not the List supports multiple formats for emails or just HTML
     * @returnf string default_from_name Default From Name for campaigns using this list
     * @returnf string default_from_email Default From Email for campaigns using this list
     * @returnf string default_subject Default Subject Line for campaigns using this list
     * @returnf string default_language Default Language for this list's forms
     */
    function lists() {
        $params = array();
        return $this->callServer("lists", $params);
    }

    /**
     * Get the list of merge tags for a given list, including their name, tag, and required setting
     *
     * @section List Related
     * @example xml-rpc_listMergeVars.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @return array list of merge tags for the list
     * @returnf string name Name of the merge field
     * @returnf char req Denotes whether the field is required (Y) or not (N)
     * @returnf string tag The merge tag that's used for forms and listSubscribe() and listUpdateMember()
     */
    function listMergeVars($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listMergeVars", $params);
    }

    /**
     * Add a new merge tag to a given list
     *
     * @section List Related
     * @example xml-rpc_listMergeVarAdd.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $tag The merge tag to add, e.g. FNAME
     * @param string $name The long description of the tag being added, used for user displays
     * @param array $req optional Various options for this merge var. <em>note:</em> for historical purposes this can also take a "boolean"
                    string field_type optional one of: text, number, radio, dropdownn, date, address, phone, url, imageurl - defaults to text
                    boolean req optional indicates whether the field is required - defaults to false
                    boolean public optional indicates whether the field is displayed in public - defaults to true
                    boolean show optional indicates whether the field is displayed in the app's list member view - defaults to true
                    string default_value optional the default value for the field. See listSubscribe() for formatting info. Defaults to blank
                    array choices optional kind of - an array of strings to use as the choices for radio and dropdown type fields
    
     * @return bool true if the request succeeds, otherwise an error will be thrown
     */
    function listMergeVarAdd($id, $tag, $name, $req=array (
)) {
        $params = array();
        $params["id"] = $id;
        $params["tag"] = $tag;
        $params["name"] = $name;
        $params["req"] = $req;
        return $this->callServer("listMergeVarAdd", $params);
    }

    /**
     * Update most parameters for a merge tag on a given list. You cannot currently change the merge type
     *
     * @section List Related
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $tag The merge tag to update
     * @param array $options The options to change for a merge var. See listMergeVarAdd() for valid options
     * @return bool true if the request succeeds, otherwise an error will be thrown
     */
    function listMergeVarUpdate($id, $tag, $options) {
        $params = array();
        $params["id"] = $id;
        $params["tag"] = $tag;
        $params["options"] = $options;
        return $this->callServer("listMergeVarUpdate", $params);
    }

    /**
     * Delete a merge tag from a given list and all its members. Seriously - the data is removed from all members as well! 
     * Note that on large lists this method may seem a bit slower than calls you typically make.
     *
     * @section List Related
     * @example xml-rpc_listMergeVarDel.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $tag The merge tag to delete
     * @return bool true if the request succeeds, otherwise an error will be thrown
     */
    function listMergeVarDel($id, $tag) {
        $params = array();
        $params["id"] = $id;
        $params["tag"] = $tag;
        return $this->callServer("listMergeVarDel", $params);
    }

    /**
     * Get the list of interest groups for a given list, including the label and form information
     *
     * @section List Related
     * @example xml-rpc_listInterestGroups.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @return struct list of interest groups for the list
     * @returnf string name Name for the Interest groups
     * @returnf string form_field Gives the type of interest group: checkbox,radio,select
     * @returnf array groups Array of the group names
     */
    function listInterestGroups($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listInterestGroups", $params);
    }

    /** Add a single Interest Group - if interest groups for the List are not yet enabled, adding the first
     *  group will automatically turn them on.
     *
     * @section List Related
     * @example xml-rpc_listInterestGroupAdd.php
     * 
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $group_name the interest group to add
     * @return bool true if the request succeeds, otherwise an error will be thrown
     */
    function listInterestGroupAdd($id, $group_name) {
        $params = array();
        $params["id"] = $id;
        $params["group_name"] = $group_name;
        return $this->callServer("listInterestGroupAdd", $params);
    }

    /** Delete a single Interest Group - if the last group for a list is deleted, this will also turn groups for the list off.
     *
     * @section List Related
     * @example xml-rpc_listInterestGroupDel.php
     * 
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $group_name the interest group to delete
     * @return bool true if the request succeeds, otherwise an error will be thrown
     */
    function listInterestGroupDel($id, $group_name) {
        $params = array();
        $params["id"] = $id;
        $params["group_name"] = $group_name;
        return $this->callServer("listInterestGroupDel", $params);
    }

    /** Change the name of an Interest Group
     *
     * @section List Related
     * 
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $old_name the interest group name to be changed
     * @param string $new_name the new interest group name to be set
     * @return bool true if the request succeeds, otherwise an error will be thrown
     */
    function listInterestGroupUpdate($id, $old_name, $new_name) {
        $params = array();
        $params["id"] = $id;
        $params["old_name"] = $old_name;
        $params["new_name"] = $new_name;
        return $this->callServer("listInterestGroupUpdate", $params);
    }

    /** Return the Webhooks configured for the given list
     *
     * @section List Related
     * 
     * @param string $id the list id to connect to. Get by calling lists()
     * @return array list of webhooks
     * @returnf string url the URL for this Webhook
     * @returnf array actions the possible actions and whether they are enabled
     * @returnf array sources the possible sources and whether they are enabled
     */
    function listWebhooks($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listWebhooks", $params);
    }

    /** Add a new Webhook URL for the given list
     *
     * @section List Related
     * 
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $url a valid URL for the Webhook - it will be validated. note that a url may only exist on a list once.
     * @param array $actions optional a hash of actions to fire this Webhook for
            boolean subscribe optional as subscribes occur, defaults to true
            boolean unsubscribe optional as subscribes occur, defaults to true
            boolean profile optional as profile updates occur, defaults to true
            boolean cleaned optional as emails are cleaned from the list, defaults to true
            boolean upemail optional when  subscribers change their email address, defaults to true
     * @param array $sources optional a hash of sources to fire this Webhook for
            boolean user optional user/subscriber initiated actions, defaults to true
            boolean admin optional admin actions in our web app, defaults to true
            boolean api optional actions that happen via API calls, defaults to false
     * @return bool true if the call succeeds, otherwise an exception will be thrown
     */
    function listWebhookAdd($id, $url, $actions=array (
), $sources=array (
)) {
        $params = array();
        $params["id"] = $id;
        $params["url"] = $url;
        $params["actions"] = $actions;
        $params["sources"] = $sources;
        return $this->callServer("listWebhookAdd", $params);
    }

    /** Delete an existing Webhook URL from a given list
     *
     * @section List Related
     * 
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $url the URL of a Webhook on this list
     * @return boolean true if the call succeeds, otherwise an exception will be thrown
     */
    function listWebhookDel($id, $url) {
        $params = array();
        $params["id"] = $id;
        $params["url"] = $url;
        return $this->callServer("listWebhookDel", $params);
    }

    /**
     * Subscribe the provided email to a list. By default this sends a confirmation email - you will not see new members until the link contained in it is clicked!
     *
     * @section List Related
     *
     * @example mcapi_listSubscribe.php
     * @example xml-rpc_listSubscribe.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the email address to subscribe
     * @param array $merge_vars array of merges for the email (FNAME, LNAME, etc.) (see examples below for handling "blank" arrays). Note that a merge field can only hold up to 255 characters. Also, there are 2 "special" keys:
                        string INTERESTS Set Interest Groups by passing a field named "INTERESTS" that contains a comma delimited list of Interest Groups to add. Commas in Interest Group names should be escaped with a backslash. ie, "," =&gt; "\,"
                        string OPTINIP Set the Opt-in IP fields. <em>Abusing this may cause your account to be suspended.</em> We do validate this and it must not be a private IP address.
                        
                        <strong>Handling Field Data Types</strong> - most fields you can just pass a string and all is well. For some, though, that is not the case...
                        Field values should be formatted as follows:
                        string address For the string version of an Address, the fields should be delimited by <strong>2</strong> spaces. Address 2 can be skipped. The Country should be a 2 character ISO-3166-1 code and will default to your default country if not set
                        array address For the array version of an Address, the requirements for Address 2 and Country are the same as with the string version. Then simply pass us an array with the keys <strong>addr1</strong>, <strong>addr2</strong>, <strong>city</strong>, <strong>state</strong>, <strong>zip</strong>, <strong>country</strong> and appropriate values for each
    
                        string date use YYYY-MM-DD to be safe. Generally, though, anything strtotime() understands we'll understand - <a href="http://us2.php.net/strtotime" target="_blank">http://us2.php.net/strtotime</a>
                        string dropdown can be a normal string - we <em>will</em> validate that the value is a valid option
                        string image must be a valid, existing url. we <em>will</em> check its existence
                        string multi_choice can be a normal string - we <em>will</em> validate that the value is a valid option
                        double number pass in a valid number - anything else will turn in to zero (0). Note, this will be rounded to 2 decimal places
                        string phone If your account has the US Phone numbers option set, this <em>must</em> be in the form of NPA-NXX-LINE (404-555-1212). If not, we assume an International number and will simply set the field with what ever number is passed in.
                        string website This is a standard string, but we <em>will</em> verify that it looks like a valid URL
                        
                        
                        
     * @param string $email_type optional - email type preference for the email (html, text, or mobile defaults to html)
     * @param boolean $double_optin optional - flag to control whether a double opt-in confirmation message is sent, defaults to true. <em>Abusing this may cause your account to be suspended.</em>
     * @param boolean $update_existing optional - flag to control whether a existing subscribers should be updated instead of throwing and error
     * @param boolean $replace_interests - flag to determine whether we replace the interest groups with the groups provided, or we add the provided groups to the member's interest groups (optional, defaults to true)
     * @param boolean $send_welcome - if your double_optin is false and this is true, we will send your lists Welcome Email if this subscribe succeeds - this will *not* fire if we end up updating an existing subscriber. defaults to false
    
     * @return boolean true on success, false on failure. When using MCAPI.class.php, the value can be tested and error messages pulled from the MCAPI object (see below)
     */
    function listSubscribe($id, $email_address, $merge_vars, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["merge_vars"] = $merge_vars;
        $params["email_type"] = $email_type;
        $params["double_optin"] = $double_optin;
        $params["update_existing"] = $update_existing;
        $params["replace_interests"] = $replace_interests;
        $params["send_welcome"] = $send_welcome;
        return $this->callServer("listSubscribe", $params);
    }

    /**
     * Unsubscribe the given email address from the list
     *
     * @section List Related
     * @example mcapi_listUnsubscribe.php
     * @example xml-rpc_listUnsubscribe.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the email address to unsubscribe
     * @param boolean $delete_member flag to completely delete the member from your list instead of just unsubscribing, default to false
     * @param boolean $send_goodbye flag to send the goodbye email to the email address, defaults to true
     * @param boolean $send_notify flag to send the unsubscribe notification email to the address defined in the list email notification settings, defaults to true
     * @return boolean true on success, false on failure. When using MCAPI.class.php, the value can be tested and error messages pulled from the MCAPI object (see below)
     */
    function listUnsubscribe($id, $email_address, $delete_member=false, $send_goodbye=true, $send_notify=true) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["delete_member"] = $delete_member;
        $params["send_goodbye"] = $send_goodbye;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listUnsubscribe", $params);
    }

    /**
     * Edit the email address, merge fields, and interest groups for a list member
     *
     * @section List Related
     * @example mcapi_listUpdateMember.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the current email address of the member to update
     * @param array $merge_vars array of new field values to update the member with.  Use "EMAIL" to update the email address and "INTERESTS" to update the interest groups
     * @param string $email_type change the email type preference for the member ("html", "text", or "mobile").  Leave blank to keep the existing preference (optional)
     * @param boolean $replace_interests flag to determine whether we replace the interest groups with the updated groups provided, or we add the provided groups to the member's interest groups (optional, defaults to true)
     * @return boolean true on success, false on failure. When using MCAPI.class.php, the value can be tested and error messages pulled from the MCAPI object
     */
    function listUpdateMember($id, $email_address, $merge_vars, $email_type='', $replace_interests=true) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["merge_vars"] = $merge_vars;
        $params["email_type"] = $email_type;
        $params["replace_interests"] = $replace_interests;
        return $this->callServer("listUpdateMember", $params);
    }

    /**
     * Subscribe a batch of email addresses to a list at once
     *
     * @section List Related
     *
     * @example mcapi_listBatchSubscribe.php
     * @example xml-rpc_listBatchSubscribe.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param array $batch an array of structs for each address to import with two special keys: "EMAIL" for the email address, and "EMAIL_TYPE" for the email type option (html, text, or mobile) 
     * @param boolean $double_optin flag to control whether to send an opt-in confirmation email - defaults to true
     * @param boolean $update_existing flag to control whether to update members that are already subscribed to the list or to return an error, defaults to false (return error)
     * @param boolean $replace_interests flag to determine whether we replace the interest groups with the updated groups provided, or we add the provided groups to the member's interest groups (optional, defaults to true)
     * @return struct Array of result counts and any errors that occurred
     * @returnf integer success_count Number of email addresses that were succesfully added/updated
     * @returnf integer error_count Number of email addresses that failed during addition/updating
     * @returnf array errors Array of error structs. Each error struct will contain "code", "message", and the full struct that failed
     */
    function listBatchSubscribe($id, $batch, $double_optin=true, $update_existing=false, $replace_interests=true) {
        $params = array();
        $params["id"] = $id;
        $params["batch"] = $batch;
        $params["double_optin"] = $double_optin;
        $params["update_existing"] = $update_existing;
        $params["replace_interests"] = $replace_interests;
        return $this->callServer("listBatchSubscribe", $params);
    }

    /**
     * Unsubscribe a batch of email addresses to a list
     *
     * @section List Related
     * @example mcapi_listBatchUnsubscribe.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param array $emails array of email addresses to unsubscribe
     * @param boolean $delete_member flag to completely delete the member from your list instead of just unsubscribing, default to false
     * @param boolean $send_goodbye flag to send the goodbye email to the email addresses, defaults to true
     * @param boolean $send_notify flag to send the unsubscribe notification email to the address defined in the list email notification settings, defaults to false
     * @return struct Array of result counts and any errors that occurred
     * @returnf integer success_count Number of email addresses that were succesfully added/updated
     * @returnf integer error_count Number of email addresses that failed during addition/updating
     * @returnf array errors Array of error structs. Each error struct will contain "code", "message", and "email"
     */
    function listBatchUnsubscribe($id, $emails, $delete_member=false, $send_goodbye=true, $send_notify=false) {
        $params = array();
        $params["id"] = $id;
        $params["emails"] = $emails;
        $params["delete_member"] = $delete_member;
        $params["send_goodbye"] = $send_goodbye;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listBatchUnsubscribe", $params);
    }

    /**
     * Get all of the list members for a list that are of a particular status
     *
     * @section List Related
     * @example mcapi_listMembers.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $status the status to get members for - one of(subscribed, unsubscribed, cleaned, updated), defaults to subscribed
     * @param string $since optional pull all members whose status (subscribed/unsubscribed/cleaned) has changed or whose profile (updated) has changed since this date/time (in GMT) - format is YYYY-MM-DD HH:mm:ss (24hr)
     * @param integer $start optional for large data sets, the page number to start at - defaults to 1st page of data (page 0)
     * @param integer $limit optional for large data sets, the number of results to return - defaults to 100, upper limit set at 15000
     * @return array Array of list member structs (see Returned Fields for details)
     * @returnf string email Member email address
     * @returnf date timestamp timestamp of their associated status date (subscribed, unsubscribed, cleaned, or updated) in GMT
     */
    function listMembers($id, $status='subscribed', $since=NULL, $start=0, $limit=100) {
        $params = array();
        $params["id"] = $id;
        $params["status"] = $status;
        $params["since"] = $since;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("listMembers", $params);
    }

    /**
     * Get all the information for a particular member of a list
     *
     * @section List Related
     * @example mcapi_listMemberInfo.php
     * @example xml-rpc_listMemberInfo.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the member email address to get information for
     * @return array array of list member info (see Returned Fields for details)
     * @returnf string id The unique id for this email address on an account
     * @returnf string email The email address associated with this record
     * @returnf string email_type The type of emails this customer asked to get: html, text, or mobile
     * @returnf array merges An associative array of all the merge tags and the data for those tags for this email address. <em>Note</em>: Interest Groups are returned as comma delimited strings - if a group name contains a comma, it will be escaped with a backslash. ie, "," =&gt; "\,"
     * @returnf string status The subscription status for this email address, either subscribed, unsubscribed or cleaned
     * @returnf string ip_opt IP Address this address opted in from. 
     * @returnf string ip_signup IP Address this address signed up from.
     * @returnf array lists An associative array of the other lists this member belongs to - the key is the list id and the value is their status in that list.
     * @returnf date timestamp The time this email address was added to the list
     */
    function listMemberInfo($id, $email_address) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        return $this->callServer("listMemberInfo", $params);
    }

    /**
     * Get all email addresses that complained about a given campaign
     *
     * @section List Related
     *
     * @example mcapi_listAbuseReports.php
     *
     * @param string $id the list id to pull abuse reports for (can be gathered using lists())
     * @param integer $start optional for large data sets, the page number to start at - defaults to 1st page of data  (page 0)
     * @param integer $limit optional for large data sets, the number of results to return - defaults to 500, upper limit set at 1000
     * @param string $since optional pull only messages since this time - use YYYY-MM-DD HH:II:SS format in <strong>GMT</strong>
     * @return array reports the abuse reports for this campaign
     * @returnf string date date/time the abuse report was received and processed
     * @returnf string email the email address that reported abuse
     * @returnf string campaign_id the unique id for the campaign that reporte was made against
     * @returnf string type an internal type generally specifying the orginating mail provider - may not be useful outside of filling report views
     */
    function listAbuseReports($id, $start=0, $limit=500, $since=NULL) {
        $params = array();
        $params["id"] = $id;
        $params["start"] = $start;
        $params["limit"] = $limit;
        $params["since"] = $since;
        return $this->callServer("listAbuseReports", $params);
    }

    /**
     * Access the Growth History by Month for a given list.
     *
     * @section List Related
     *
     * @example mcapi_listGrowthHistory.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @return array array of months and growth 
     * @returnf string month The Year and Month in question using YYYY-MM format
     * @returnf integer existing number of existing subscribers to start the month
     * @returnf integer imports number of subscribers imported during the month
     * @returnf integer optins number of subscribers who opted-in during the month
     */
    function listGrowthHistory($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listGrowthHistory", $params);
    }

    /**
     * <strong>DEPRECATED:</strong> Retrieve your User Unique Id and your Affiliate link to display/use for 
     * <a href="/monkeyrewards/" target="_blank">Monkey Rewards</a>. While
     * we don't use the User Id for any API functions, it can be useful if building up URL strings for things such as the profile editor and sub/unsub links.
     *
     * @section Helper
     *
     * @deprecated See getAccountDetails() for replacement
     *
     * @example mcapi_getAffiliateInfo.php
     * @example xml-rpc_getAffiliateInfo.php
     *
     * @return array containing your Affilliate Id and full link.
     * @returnf string user_id Your User Unique Id. 
     * @returnf string url Your Monkey Rewards link for our Affiliate program
     */
    function getAffiliateInfo() {
        $params = array();
        return $this->callServer("getAffiliateInfo", $params);
    }

    /**
     * Retrieve lots of account information including payments made, plan info, some account stats, installed modules,
     * contact info, and more. No private information like Credit Card numbers is available.
     * 
     * @section Helper
     *
     * @return array containing the details for the account tied to this API Key
     * @returnf string username The Account username
     * @returnf string user_id The Account user unique id (for building some links)
     * @returnf bool is_trial Whether the Account is in Trial mode (can only send campaigns to less than 100 emails)
     * @returnf string timezone The timezone for the Account - default is "US/Eastern"
     * @returnf string plan_type Plan Type - "monthly", "payasyougo", or "free"
     * @returnf int plan_low <em>only for Monthly plans</em> - the lower tier for list size
     * @returnf int plan_high <em>only for Monthly plans</em> - the upper tier for list size
     * @returnf datetime plan_start_date <em>only for Monthly plans</em> - the start date for a monthly plan
     * @returnf int emails_left <em>only for Free and Pay-as-you-go plans</em> emails credits left for the account
     * @returnf bool pending_monthly Whether the account is finishing Pay As You Go credits before switching to a Monthly plan
     * @returnf datetime first_payment date of first payment
     * @returnf datetime last_payment date of most recent payment
     * @returnf int times_logged_in total number of times the account has been logged into via the web
     * @returnf datetime last_login date/time of last login via the web
     * @returnf string affiliate_link Monkey Rewards link for our Affiliate program
     * @returnf array contact Contact details for the account, including: First & Last name, email, company name, address, phone, and url
     * @returnf array addons Addons installed in the account and the date they were installed.
     * @returnf array orders Order details for the account, include order_id, type, cost, date/time, and any credits applied to the order
     */
    function getAccountDetails() {
        $params = array();
        return $this->callServer("getAccountDetails", $params);
    }

    /**
     * Have HTML content auto-converted to a text-only format. You can send: plain HTML, an array of Template content, an existing Campaign Id, or an existing Template Id. Note that this will <b>not</b> save anything to or update any of your lists, campaigns, or templates.
     *
     * @section Helper
     * @example xml-rpc_generateText.php
     *
     * @param string $type The type of content to parse. Must be one of: "html", "template", "url", "cid" (Campaign Id), or "tid" (Template Id)
     * @param mixed $content The content to use. For "html" expects  a single string value, "template" expects an array like you send to campaignCreate, "url" expects a valid & public URL to pull from, "cid" expects a valid Campaign Id, and "tid" expects a valid Template Id on your account.
     * @return string the content pass in converted to text.
     */
    function generateText($type, $content) {
        $params = array();
        $params["type"] = $type;
        $params["content"] = $content;
        return $this->callServer("generateText", $params);
    }

    /**
     * Send your HTML content to have the CSS inlined and optionally remove the original styles.
     *
     * @section Helper
     * @example xml-rpc_inlineCss.php
     *
     * @param string $html Your HTML content
     * @param bool $strip_css optional Whether you want the CSS &lt;style&gt; tags stripped from the returned document. Defaults to false.
     * @return string Your HTML content with all CSS inlined, just like if we sent it.
     */
    function inlineCss($html, $strip_css=false) {
        $params = array();
        $params["html"] = $html;
        $params["strip_css"] = $strip_css;
        return $this->callServer("inlineCss", $params);
    }

    /**
     * Create a new folder to file campaigns in
     *
     * @section Helper
     * @example mcapi_createFolder.php
     * @example xml-rpc_createFolder.php
     *
     * @param string $name a unique name for a folder
     * @return integer the folder_id of the newly created folder.
     */
    function createFolder($name) {
        $params = array();
        $params["name"] = $name;
        return $this->callServer("createFolder", $params);
    }

    /**
     * Retrieve a list of all MailChimp API Keys for this User
     *
     * @section Security Related
     * @example xml-rpc_apikeyAdd.php
     * @example mcapi_apikeyAdd.php
     * 
     * @param string $username Your MailChimp user name
     * @param string $password Your MailChimp password
     * @param boolean $expired optional - whether or not to include expired keys, defaults to false
     * @return array an array of API keys including:
     * @returnf string apikey The api key that can be used
     * @returnf string created_at The date the key was created
     * @returnf string expired_at The date the key was expired
     */
    function apikeys($username, $password, $expired=false) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        $params["expired"] = $expired;
        return $this->callServer("apikeys", $params);
    }

    /**
     * Add an API Key to your account. We will generate a new key for you and return it.
     *
     * @section Security Related
     * @example xml-rpc_apikeyAdd.php
     *
     * @param string $username Your MailChimp user name
     * @param string $password Your MailChimp password
     * @return string a new API Key that can be immediately used.
     */
    function apikeyAdd($username, $password) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        return $this->callServer("apikeyAdd", $params);
    }

    /**
     * Expire a Specific API Key. Note that if you expire all of your keys, a new, valid one will be created and returned
     * next time you call login(). If you are trying to shut off access to your account for an old developer, change your 
     * MailChimp password, then expire all of the keys they had access to. Note that this takes effect immediately, so make 
     * sure you replace the keys in any working application before expiring them! Consider yourself warned... 
     *
     * @section Security Related
     * @example mcapi_apikeyExpire.php
     * @example xml-rpc_apikeyExpire.php
     *
     * @param string $username Your MailChimp user name
     * @param string $password Your MailChimp password
     * @return boolean true if it worked, otherwise an error is thrown.
     */
    function apikeyExpire($username, $password) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        return $this->callServer("apikeyExpire", $params);
    }

    /**
     * "Ping" the MailChimp API - a simple method you can call that will return a constant value as long as everything is good. Note
     * than unlike most all of our methods, we don't throw an Exception if we are having issues. You will simply receive a different
     * string back that will explain our view on what is going on.
     *
     * @section Helper
     * @example xml-rpc_ping.php
     *
     * @return string returns "Everything's Chimpy!" if everything is chimpy, otherwise returns an error message
     */
    function ping() {
        $params = array();
        return $this->callServer("ping", $params);
    }

    /**
     * Internal function - proxy method for certain XML-RPC calls | DO NOT CALL
     * @param mixed Method to call, with any parameters to pass along
     * @return mixed the result of the call
     */
    function callMethod() {
        $params = array();
        return $this->callServer("callMethod", $params);
    }
    
    /**
     * Actually connect to the server and call the requested methods, parsing the result
     * You should never have to call this function manually
     */
    function callServer($method, $params) {
    	//Always include the apikey if we are not logging in
    	if($method != "login") {
    	    $dc = "us1";
    	    if (strstr($this->api_key,"-")){
            	list($key, $dc) = explode("-",$this->api_key,2);
                if (!$dc) $dc = "us1";
            }
            $host = $dc.".".$this->apiUrl["host"];
    		$params["apikey"] = $this->api_key;
    	} else {
        	$host = $this->apiUrl["host"];
    	}
        $this->errorMessage = "";
        $this->errorCode = "";
        $post_vars = $this->httpBuildQuery($params);
        
        $payload = "POST " . $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . "&method=" . $method . " HTTP/1.0\r\n";
        $payload .= "Host: " . $host . "\r\n";
        $payload .= "User-Agent: MCAPI/" . $this->version ."\r\n";
        $payload .= "Content-type: application/x-www-form-urlencoded\r\n";
        $payload .= "Content-length: " . strlen($post_vars) . "\r\n";
        $payload .= "Connection: close \r\n\r\n";
        $payload .= $post_vars;
        
        ob_start();
        if ($this->secure){
            $sock = fsockopen("ssl://".$host, 443, $errno, $errstr, $this->timeout);
        } else {
            $sock = fsockopen($host, 80, $errno, $errstr, $this->timeout);
        }
        if(!$sock) {
            $this->errorMessage = "Could not connect (ERR $errno: $errstr)";
            $this->errorCode = "-99";
            ob_end_clean();
            return false;
        }
        
        $response = "";
        fwrite($sock, $payload);
        while(!feof($sock)) {
            $response .= fread($sock, $this->chunkSize);
        }
        fclose($sock);
        ob_end_clean();
        
        list($throw, $response) = explode("\r\n\r\n", $response, 2);
        
        if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);
        
        $serial = unserialize($response);
        if($response && $serial === false) {
        	$response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
        } else {
        	$response = $serial;
        }
        if(is_array($response) && isset($response["error"])) {
            $this->errorMessage = $response["error"];
            $this->errorCode = $response["code"];
            return false;
        }
        
        return $response;
    }
    
    /**
     * Re-implement http_build_query for systems that do not already have it
     */
    function httpBuildQuery($params, $key=null) {
        $ret = array();
        
        foreach((array) $params as $name => $val) {
            $name = urlencode($name);
            if($key !== null) {
                $name = $key . "[" . $name . "]";
            }
            
            if(is_array($val) || is_object($val)) {
                $ret[] = $this->httpBuildQuery($val, $name);
            } elseif($val !== null) {
                $ret[] = $name . "=" . urlencode($val);
            }
        }
        
        return implode("&", $ret);
    }
}

?>