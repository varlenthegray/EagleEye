<?php

require_once "../vendor/autoload.php";
require_once "../src/Configuration.php";
use FlowrouteNumbersAndMessagingLib\Models;

// Access your Flowroute API credentials as local environment variables
$username = '46449714';
$password = 'aQlpTe9RgTVWfDL6jw0BEKSo3r4hPfxN';

// create our client object
$client = new FlowrouteNumbersAndMessagingLib\FlowrouteNumbersAndMessagingClient($username, $password);

// List all our numbers
$our_numbers = GetNumbers($client);

// List all our SMS Messages
$our_messages = GetMessages($client);

// Send an SMS Message from our account
SendSMS($client, $our_numbers[0]);

// Send an MMS Message from out account
SendMMS($client, $our_numbers[0]);

// Look up a specific MDR
GetMDRDetail($client, $our_messages[0]);

// Find details for a specific number
$number_details = GetNumberDetails($client, $our_numbers[0]->attributes->value);

// Find purchasable numbers
$available_numbers = GetAvailableNumbers($client);

// List Available Area Codes
$available_areacodes = GetAvailableAreaCodes($client);

// List available Exchange Codes
$available_exchange_codes = GetAvailableExchangeCodes($client);

// List Inbound Routes
$inbound_routes = GetInboundRoutes($client);

// Create an Inbound Route
CreateInboundRoute($client);

// Update Primary Route for a DID
$route_id = "";
foreach($inbound_routes as $item)
{
    if($item->attributes->route_type == "host") {
        $route_id = $item->id;
        break;
    }
}
echo "Route ID: " . $route_id . "\n";
UpdatePrimaryRoute($client, $our_numbers[0]->id, $route_id);

// Update the Failover Route for a DID
for ($i = 1; $i < count($inbound_routes); $i++ )
{
    $item = $inbound_routes[$i];
    if($item->attributes->route_type == "host") {
        $route_id = $item->id;
        break;
    }
}
UpdateFailoverRoute($client, $our_numbers[1]->id, $route_id);

echo "\n\nAll Tests Completed\n";

function CreateInboundRoute($client)
{
    $routes = $client->getRoutes();
    $body = new Models\NewRoute();
    $body->data = new Models\Data61();
    $body->data->attributes = new Models\Attributes62();
    $body->data->attributes->alias = "Test Route";
    $body->data->attributes->routeType = Models\RouteTypeEnum::HOST;
    $body->data->attributes->value = "www.flowroute.com";

    $result = $routes->CreateAnInboundRoute($body);
    var_dump($result);
}
 
function UpdatePrimaryRoute($client, $DID, $route_id)
{
    $routes = $client->getRoutes();
    $result = $routes->UpdatePrimaryVoiceRouteForAPhoneNumber($DID, $route_id);
}

function UpdateFailoverRoute($client, $DID, $route_id)
{
    $routes = $client->getRoutes();
    $result = $routes->UpdateFailoverVoiceRouteForAPhoneNumber($DID, $route_id);
}

function GetInboundRoutes($client)
{
    $return_list = array();

    $limit = 3;
    $offset = 0;

    $routes = $client->getRoutes();

    do
    {
        $route_data = $routes->ListInboundRoutes($limit, $offset);
        var_dump($route_data);

        foreach ($route_data->data as $item)
        {
            echo "---------------------------\nInbound Routes:\n";
            var_dump($item);
            $return_list[] = $item;
        }

        // See if there is more data to process
        $links = $route_data->links;
        if (isset($links->next))
        {
            // more data to pull
            $offset += $limit;
        }
        else
        {
            break;   // no more data
        }
    }
    while (true);

    return $return_list;
}

function GetAvailableExchangeCodes($client)
{
    $return_list = array();

    $limit = 10;
    $offset = 0;
    $maxSetupCost = 174.40;
    $areacode = "206";

    // User the Numbers Controller from our Client
    $numbers = $client->getNumbers();

    do
    {
        $exchanges_data = $numbers->ListAvailableExchangeCodes($limit, $offset, $maxSetupCost, $areacode);
        var_dump($exchanges_data);

        foreach ($exchanges_data as $item)
        {
            echo "---------------------------\nAvailable Exchange:\n";
            var_dump($item);
            $return_list[] = $item;
        }

        // See if there is more data to process
        $links = $exchanges_data->links;
        if (isset($links->next))
        {
            // more data to pull
            $offset += $limit;
        }
        else
        {
            break;   // no more data
        }
    }
    while (true);

    return $return_list;
}

function GetAvailableAreaCodes($client)
{
    $return_list = array();

    $limit = 2;
    $offset = 0;
    $maxSetupCost = 10.00;

    // User the Numbers Controller from our Client
    $numbers = $client->getNumbers();

    do
    {
        echo "Offset is " . $offset;
        $areacode_data = $numbers->ListAvailableAreaCodes($limit, $offset, $maxSetupCost);
        var_dump($areacode_data);

        foreach ($areacode_data as $item)
        {
            echo "---------------------------\nAvailable Area Code:\n";
            var_dump($item);
            $return_list[] = $item;
        }

        // See if there is more data to process
        $links = $areacode_data->links;
        if (isset($links->next))
        {
            // more data to pull
            $offset += $limit;
        }
        else
        {
            break;   // no more data
        }
    } while (true);

    return $return_list;
}
 

function GetAvailableNumbers($client)
{
    $startsWith = "206";
    $contains = NULL;
    $endsWith = NULL;
    $rateCenter = NULL;
    $state = NULL;

    $limit = 2;
    $offset = 0;

    $return_list = array();
    // User the Numbers Controller from our Client
    $numbers = $client->getNumbers();
    do
    {
        $number_data = $numbers->SearchForPurchasablePhoneNumbers($startsWith, $contains,
                $endsWith, $limit, $offset, $rateCenter, $state);
        var_dump($number_data);
        // Iterate through each number item
        foreach ($number_data as $item)
        {
            echo "---------------------------\nAvailable Area Codes:\n";
            var_dump($item);
            $return_list[] = $item;
        }

        // See if there is more data to process
        $links = $number_data->links;
        if (isset($links->next))
        {
            // more data to pull
            $offset += $limit;
        }
        else
        {
            break;   // no more data
        }
    } while (true);

    return $return_list;
}

function GetMDRDetail($client, $id)
{
    $messages = $client->getMessages();
    var_dump($id);

    $mdr_data = $messages->GetLookUpAMessageDetailRecord($id);
    var_dump($mdr_data);
}
 
function SendSMS($client, $from_did)
{
    $msg = new Models\Message();
    var_dump($from_did);
    $msg->from = $from_did->id;
    $msg->to = "YOUR_MOBILE_NUMBER"; // Replace with your mobile number to receive messages from your Flowroute account
    $msg->body = "This is a Test Message";
}

function SendMMS($client, $from_did)
{
    $msg = new Models\MMS_Message();
    $msg->from = $from_did->id;
    // TODO: Replace the number below
    $msg->to = "YOUR_MOBILE_NUMBER";
    $msg->body = "This is a Test Message";
    $msg->mediaUrls[] = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';

    $messages = $client->getMessages();
    $result = $messages->CreateSendAMessage($msg);
}

function GetMessages($client)
{
    $return_list = array();
    $limit = 1;
    $offset = 0;

    // Find all messages since January 1, 2017
    $startDate = new DateTime('2018-01-01', new DateTimeZone('Pacific/Nauru'));

    $endDate = NULL;

    do
    {
        $messages = $client->getMessages();
        $message_data = $messages->getLookUpASetOfMessages($startDate, $endDate, $limit, $offset);

        // Iterate through each number item
        foreach ($message_data->data as $item)
        {
            echo "---------------------------\nSMS MDR:\n";
            var_dump($item);
            $return_list[] = $item->id;
        }

        // See if there is more data to process
        $links = $message_data->links;
        if (isset($links->next))
        {
            // more data to pull
            $offset += $limit;
        }
        else
        {
            break;   // no more data
        }
    }
    while (true);

    return $return_list;
}
 
function GetNumbers($client)
{
    $return_list = array();

    // List all phone numbers in our account paging through them 1 at a time
    //  If you have several phone numbers, change the 'limit' variable below
    //  This example is intended to show how to page through a list of resources

    // create a numbers instance
    $numbers = $client->getNumbers();

    // query all our numbers
    $startsWith = NULL;
    $endsWith = NULL;
    $contains = NULL;
    $limit = 10;
    $offset = 0;

    $result = $numbers->getAccountPhoneNumbers($startsWith, $endsWith, $contains, $limit, $offset);
    //var_dump($result);

    foreach($result as $item) {
        foreach($item as $entry) {
            var_dump($entry);
            echo "--------------------------------------\n";
            $return_list[] = $entry;
        }
        echo "--------------------------------------\n";
    }

    return $return_list;
}

function GetNumberDetails($client, $id)
{
    // User the Numbers Controller from our Client
    $numbers = $client->getNumbers();
    echo "Calling gnd with " . $id;
    $result = $numbers->getPhoneNumberDetails($id);
    var_dump($result);
    return $result;
}

?>
