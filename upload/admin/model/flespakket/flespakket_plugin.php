<?php
/**
* ----------------------------------------------------------------------------------------------------------------------------
* @purpose:   Installation of Flespakket Plugin
*
* @editors    MB
* @version    1.0
* @since      Available since release 1.0
* @support    info@flespakket.nl
* @copyright  2011 Flespakket
* @link       http://www.flespakket.nl
* ----------------------------------------------------------------------------------------------------------------------------
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');
//phpinfo();
//require('includes/application_top.php');

if (file_exists('../../config.php')) {
	require_once('../../config.php');
}
require_once(DIR_SYSTEM . 'startup.php');

define('FLESPAKKET_LINK', 'http://www.flespakket.nl/');
define( 'DS', DIRECTORY_SEPARATOR );
define('TABLE_ORDERS','#__virtuemart_orders');
$rootFolder = explode(DS,dirname(__FILE__));
//current level in diretoty structure
$currentfolderlevel = 3;
array_splice($rootFolder,-$currentfolderlevel);

$base_folder = implode(DS,$rootFolder);


if(is_dir($base_folder.DS.'libraries'.DS.'joomla'))   
{
   
   define( '_JEXEC', 1 );
   
   define('JPATH_BASE',implode(DS,$rootFolder));
   
   require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
   require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
}
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$check_if_table = $db->query("SHOW TABLES LIKE '" . DB_PREFIX . "orders_flespakket'");
if ($check_if_table->num_rows < 1)
{
   $db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "orders_flespakket (`orders_flespakket_id` int(11) NOT NULL AUTO_INCREMENT, `orders_id` int(11) NOT NULL, `consignment_id` bigint(20) NOT NULL, `retour` tinyint(1) NOT NULL DEFAULT '0', `tracktrace` varchar(32) NOT NULL, `postcode` varchar(6) NOT NULL, `tnt_status` varchar(255) NOT NULL, `tnt_updated_on` datetime NOT NULL, `tnt_final` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`orders_flespakket_id`));");
}



/*
 *   FUNCTIONS
 */

function getOrderz($order_id) {
   global $db;// = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
   $order_query = $db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

   if ($order_query->num_rows) {
	   $reward = 0;
	   
	   $order_product_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
   
	   foreach ($order_product_query->rows as $product) {
		   $reward += $product['reward'];
	   }			
	   
	   $country_query = $db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

	   if ($country_query->num_rows) {
		   $payment_iso_code_2 = $country_query->row['iso_code_2'];
		   $payment_iso_code_3 = $country_query->row['iso_code_3'];
	   } else {
		   $payment_iso_code_2 = '';
		   $payment_iso_code_3 = '';
	   }

	   $zone_query = $db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

	   if ($zone_query->num_rows) {
		   $payment_zone_code = $zone_query->row['code'];
	   } else {
		   $payment_zone_code = '';
	   }
	   
	   $country_query = $db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

	   if ($country_query->num_rows) {
		   $shipping_iso_code_2 = $country_query->row['iso_code_2'];
		   $shipping_iso_code_3 = $country_query->row['iso_code_3'];
	   } else {
		   $shipping_iso_code_2 = '';
		   $shipping_iso_code_3 = '';
	   }

	   $zone_query = $db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

	   if ($zone_query->num_rows) {
		   $shipping_zone_code = $zone_query->row['code'];
	   } else {
		   $shipping_zone_code = '';
	   }
   
	   if ($order_query->row['affiliate_id']) {
		   $affiliate_id = $order_query->row['affiliate_id'];
	   } else {
		   $affiliate_id = 0;
	   }				
		   
	   
	   
	   return array(
		   //'amazon_order_id'         => $amazonOrderId,
		   'order_id'                => $order_query->row['order_id'],
		   'invoice_no'              => $order_query->row['invoice_no'],
		   'invoice_prefix'          => $order_query->row['invoice_prefix'],
		   'store_id'                => $order_query->row['store_id'],
		   'store_name'              => $order_query->row['store_name'],
		   'store_url'               => $order_query->row['store_url'],
		   'customer_id'             => $order_query->row['customer_id'],
		   'customer'                => $order_query->row['customer'],
		   'customer_group_id'       => $order_query->row['customer_group_id'],
		   'firstname'               => $order_query->row['firstname'],
		   'lastname'                => $order_query->row['lastname'],
		   'telephone'               => $order_query->row['telephone'],
		   'fax'                     => $order_query->row['fax'],
		   'email'                   => $order_query->row['email'],
		   'payment_firstname'       => $order_query->row['payment_firstname'],
		   'payment_lastname'        => $order_query->row['payment_lastname'],
		   'payment_company'         => $order_query->row['payment_company'],
		   'payment_company_id'      => $order_query->row['payment_company_id'],
		   'payment_tax_id'          => $order_query->row['payment_tax_id'],
		   'payment_address_1'       => $order_query->row['payment_address_1'],
		   'payment_address_2'       => $order_query->row['payment_address_2'],
		   'payment_postcode'        => $order_query->row['payment_postcode'],
		   'payment_city'            => $order_query->row['payment_city'],
		   'payment_zone_id'         => $order_query->row['payment_zone_id'],
		   'payment_zone'            => $order_query->row['payment_zone'],
		   'payment_zone_code'       => $payment_zone_code,
		   'payment_country_id'      => $order_query->row['payment_country_id'],
		   'payment_country'         => $order_query->row['payment_country'],
		   'payment_iso_code_2'      => $payment_iso_code_2,
		   'payment_iso_code_3'      => $payment_iso_code_3,
		   'payment_address_format'  => $order_query->row['payment_address_format'],
		   'payment_method'          => $order_query->row['payment_method'],
		   'payment_code'            => $order_query->row['payment_code'],				
		   'shipping_firstname'      => $order_query->row['shipping_firstname'],
		   'shipping_lastname'       => $order_query->row['shipping_lastname'],
		   'shipping_company'        => $order_query->row['shipping_company'],
		   'shipping_address_1'      => $order_query->row['shipping_address_1'],
		   'shipping_address_2'      => $order_query->row['shipping_address_2'],
		   'shipping_postcode'       => $order_query->row['shipping_postcode'],
		   'shipping_city'           => $order_query->row['shipping_city'],
		   'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
		   'shipping_zone'           => $order_query->row['shipping_zone'],
		   'shipping_zone_code'      => $shipping_zone_code,
		   'shipping_country_id'     => $order_query->row['shipping_country_id'],
		   'shipping_country'        => $order_query->row['shipping_country'],
		   'shipping_iso_code_2'     => $shipping_iso_code_2,
		   'shipping_iso_code_3'     => $shipping_iso_code_3,
		   'shipping_address_format' => $order_query->row['shipping_address_format'],
		   'shipping_method'         => $order_query->row['shipping_method'],
		   'shipping_code'           => $order_query->row['shipping_code'],
		   'comment'                 => $order_query->row['comment'],
		   'total'                   => $order_query->row['total'],
		   'reward'                  => $reward,
		   'order_status_id'         => $order_query->row['order_status_id'],
		   'affiliate_id'            => $order_query->row['affiliate_id'],
		   //'affiliate_firstname'     => $affiliate_firstname,
		   //'affiliate_lastname'      => $affiliate_lastname,
		   'commission'              => $order_query->row['commission'],
		   'language_id'             => $order_query->row['language_id'],
		   //'language_code'           => $language_code,
		   //'language_filename'       => $language_filename,
		   //'language_directory'      => $language_directory,				
		   'currency_id'             => $order_query->row['currency_id'],
		   'currency_code'           => $order_query->row['currency_code'],
		   'currency_value'          => $order_query->row['currency_value'],
		   'ip'                      => $order_query->row['ip'],
		   'forwarded_ip'            => $order_query->row['forwarded_ip'], 
		   'user_agent'              => $order_query->row['user_agent'],	
		   'accept_language'         => $order_query->row['accept_language'],					
		   'date_added'              => $order_query->row['date_added'],
		   'date_modified'           => $order_query->row['date_modified']
	   );
   } else {
	   return false;
   }
}
 
 
function getOrderz2($virtuemart_order_id){
		global $db;// = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		$virtuemart_order_id = (int)$virtuemart_order_id;

		$order = array();

		// Get the order details
		$q = "SELECT  u.*,o.*,
				s.order_status_name
			FROM #__virtuemart_orders o
			LEFT JOIN #__virtuemart_orderstates s
			ON s.order_status_code = o.order_status
			LEFT JOIN #__virtuemart_order_userinfos u
			ON u.virtuemart_order_id = o.virtuemart_order_id
			WHERE o.virtuemart_order_id=".$virtuemart_order_id;
		$db->query($q);
		$order['details'] = $db->loadObjectList('address_type');

		// Get the order history
		$q = "SELECT *
			FROM #__virtuemart_order_histories
			WHERE virtuemart_order_id=".$virtuemart_order_id."
			ORDER BY virtuemart_order_history_id ASC";
		$db->query($q);
		$order['history'] = $db->loadObjectList();

		// Get the order items
$q = 'SELECT virtuemart_order_item_id, product_quantity, order_item_name,
   order_item_sku, i.virtuemart_product_id, product_item_price,
   product_final_price, product_basePriceWithTax, product_discountedPriceWithoutTax, product_priceWithoutTax, product_subtotal_with_tax, product_subtotal_discount, product_tax, product_attribute, order_status,
   intnotes, virtuemart_category_id
  FROM (#__virtuemart_order_items i
  LEFT JOIN #__virtuemart_products p
  ON p.virtuemart_product_id = i.virtuemart_product_id)
                       LEFT JOIN #__virtuemart_product_categories c
                       ON p.virtuemart_product_id = c.virtuemart_product_id
  WHERE `virtuemart_order_id`="'.$virtuemart_order_id.'" group by `virtuemart_order_item_id`';
//group by `virtuemart_order_id`'; Why ever we added this, it makes trouble, only one order item is shown then.
// without group by we get the product 3 times, when it is in 3 categories and similar, so we need a group by
//lets try group by `virtuemart_order_item_id`
		$db->query($q);
		$order['items'] = $db->loadObjectList();
// Get the order items
		$q = "SELECT  *
			FROM #__virtuemart_order_calc_rules AS z
			WHERE  virtuemart_order_id=".$virtuemart_order_id;
		$db->query($q);
		$order['calc_rules'] = $db->loadObjectList();
// 		vmdebug('getOrder my order',$order);
		return $order;
	} 

 
function getAddressComponents($address)
{
    $ret = array();
    $ret['house_number']    = '';
    $ret['number_addition'] = '';

    $address = str_replace(array('?', '*', '[', ']', ',', '!'), ' ', $address);
    $address = preg_replace('/\s\s+/', ' ', $address);

    preg_match('/^([0-9]*)(.*?)([0-9]+)(.*)/', $address, $matches);

    if (!empty($matches[2]))
    {
        $ret['street']          = trim($matches[1] . $matches[2]);
        $ret['house_number']    = trim($matches[3]);
        $ret['number_addition'] = trim($matches[4]);
    }
    else // no street part
    {
        $ret['street'] = $address;
    }
    return $ret;
}

/*
 *   JAVASCRIPT ACTIONS
 */
if(isset($_GET['action']))
{
    /*
     *   FLESPAKKET STATUS UPDATE
     *
     *   Every time this script is called, it will check if an update of the order statuses is required
     *   Depending on the last update with a timeout, since TNT updates our status 2 times a day anyway
     *
     *   NOTE - Increasing this timeout is POINTLESS, since TNT updates our statuses only 2 times a day
     *          Please save our bandwidth and use the Track&Trace link to get the actual status. Thanks
     */

    if(isset($_SESSION['FLESPAKKET_VISIBLE_CONSIGNMENTS'])
    && !empty($_SESSION['FLESPAKKET_VISIBLE_CONSIGNMENTS']))
    {
        $visible_consignments = str_replace('|', ',', trim($_SESSION['FLESPAKKET_VISIBLE_CONSIGNMENTS'], '|'));
        
        
		global $db;// = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		$query = "SELECT *  FROM " . DB_PREFIX . "orders_flespakket WHERE consignment_id IN (" . $visible_consignments . ") AND tnt_final = 0 AND tnt_updated_on < '" . date('Y-m-d H:i:s', time() - 43200) . "'";
		
		$vendors = $db->query( $query );
		$consignments = array();
		for ($i=0, $n=count( $vendors ); $i < $n; $i++) 
		{
			$row = &$vendors[$i];
			$consignments[] = $row->consignment_id;
        
        
        //$status_q = tep_db_query("SELECT *  ROM orders_flespakket WHERE consignment_id IN (" . $visible_consignments . ") AND tnt_final = 0 AND tnt_updated_on < '" . date('Y-m-d H:i:s', time() - 43200) . "'");
        
        /*while($consignment = tep_db_fetch_array($status_q))
        {
            $consignments[] = $consignment['consignment_id'];
        }*/
        }
        if(!empty($consignments))
        {
            $status_file = file(FLESPAKKET_LINK . 'status/tnt/' . implode('|', $consignments));

            foreach($status_file as $row)
            {
                $row = explode('|', $row);
                if(count($row) != 3) exit;
                
                $qupdate = "UPDATE " . DB_PREFIX . "orders_flespakket SET tnt_status='".trim($row[2])."', tnt_updated_on='".date('Y-m-d H:i:s')."', tnt_final='".(int) $row[1]."' WHERE consignment_id = '" . $row[0] . "'";
                $db->query( $qupdate );
		$db->query();
                
               /* tep_db_perform('orders_flespakket', array(
                	'tnt_status'     => trim($row[2]),
                	'tnt_updated_on' => date('Y-m-d H:i:s'),
                    'tnt_final'      => (int) $row[1],
                ), 'update', "consignment_id = '" . $row[0] . "'");
*/
            }
        }
    }

    /*
     *   PLUGIN POPUP CREATE / RETOUR
     */

    if($_GET['action'] == 'post' && is_numeric($_GET['order_id']))
    {
        $order_id_full_array = explode('.', $_GET['order_id']);
	 $order_id = $order_id_full_array[0];
	 $order_pck = $order_id_full_array[1];
	 $mano_package = '';
	if ($order_pck == 999)
	{
		$mano_package = 'other';
	}
	else
	{
		$mano_package = 'bottle_'.$order_pck;
	}
	
	//include(DIR_WS_CLASSES . 'order.php');
        // determine retour or normal consignment
        if(isset($_GET['retour']) && $_GET['retour'] == 'true')
        {
            $flespakket_plugin_action = 'verzending-aanmaken-retour/';
            $flespakket_action = 'retour';
        }
        else
        {
            $flespakket_plugin_action = 'verzending-aanmaken/';
            $flespakket_action = 'return';
        }

        $return_url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?action=' . $flespakket_action . '&order_id=' . $order_id . '&timestamp=' . $_GET['timestamp'];
        //echo "<script>console.log('".$return_url."')</script>";
	$order = getOrderz($order_id);
        //echo "aaa";
        //print_r($order['details']['BT']->virtuemart_country_id);
        //die;
        //$address = $order->delivery;
	//echo "<br/><br/>";print_r($order);echo "<br/><br/>";print_r($order['shipping_iso_code_2']);echo "<br/><br/>";
	
	$musu_country_code='';
	if (strlen($order['shipping_iso_code_2']) > 0) {
		  $musu_country_code = $order['shipping_iso_code_2']; 
	 } else {
		 $musu_country_code = $order['payment_iso_code_2']; 
	 }
	
	

        /*$country_sql = tep_db_query("
SELECT countries_iso_code_2 AS country_code
  FROM " . TABLE_COUNTRIES . "
 WHERE countries_name = '" . $address['country'] . "'
");
        $country = tep_db_fetch_array($country_sql);*/

if (strlen($order['shipping_company']) > 0) {
	$gkcompany = $order['shipping_company']; 
} else {
	$gkcompany = $order['payment_company']; 
}

if (strlen($order['shipping_postcode']) > 0) {
	$gkzip = $order['shipping_postcode']; 
} else {
	$gkzip = $order['payment_postcode']; 
}

if (strlen($order['shipping_city']) > 0) {
	$gkcity = $order['shipping_city']; 
} else {
	$gkcity = $order['payment_city']; 
}

/*if (strlen($order['details']['ST']->email) > 0) {
	$gkemail = $order['details']['ST']->email; 
} else {
	$gkemail = $order['details']['BT']->email; 
}*/
$gkemail = $order['email']; 

if (strlen($order['shipping_firstname']) > 0) {
	$gkfirstname = $order['shipping_firstname']; 
} else {
	$gkfirstname = $order['payment_firstname']; 
}
if (strlen($order['shipping_lastname']) > 0) {
	$gklastname = $order['shipping_lastname']; 
} else {
	$gklastname = $order['payment_lastname']; 
}
if (strlen($order['shipping_address_1']) > 0) {
	$gkaddr = $order['shipping_address_1']; 
} else {
	$gkaddr = $order['payment_address_1']; 
}
/*if (strlen($order['details']['ST']->phone_1) > 0) {
	$gkphone = $order['details']['ST']->phone_1; 
} else {
	$gkphone = $order['details']['BT']->phone_1; 
}*/
$gkphone = $order['telephone'];


//$gkadresas_num = preg_replace('/\D/', '', $gkaddr);

//$gkadresas_street = preg_replace('/[^A-Z a-z]/', '', $gkaddr);


        if($musu_country_code=='NL')
        {
            $street = getAddressComponents($gkaddr);
            $consignment = array(
	       'package'        => $mano_package, // bottle_1 | bottle_2 | bottle_3 | bottle_6 | bottle_12 | other
            	'ToAddress[country_code]'    => $musu_country_code,
            	'ToAddress[name]'            => $gkfirstname." ".$gklastname,
            	'ToAddress[business]'        => $gkcompany,
            	'ToAddress[postcode]'        => $gkzip,
            	'ToAddress[house_number]'    => $street['house_number'],
            	'ToAddress[number_addition]' => $street['number_addition'],
            	'ToAddress[street]'          => $street['street'],
            	'ToAddress[town]'            => $gkcity,
            	'ToAddress[email]'           => $gkemail,
            	'ToAddress[phone_number]' => $gkphone,
		'custom_id' => str_pad($order['order_id'], 7, '0', STR_PAD_LEFT),//$order['details']['BT']->order_number,
            );
        }
        else // buitenland
        {
            $weight = 0;
	    global $db;// = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	    $queryz = "SELECT SUM(A.`quantity` * B.`weight`) AS `total_weight` FROM `" . DB_PREFIX . "order_product` AS A, `" . DB_PREFIX . "product` AS B WHERE (A.`order_id` ='".$order['order_id']."') AND (A.`product_id` = B.`product_id`)";
	    $rezultatas = $db->query( $queryz );
	    $weight = $rezultatas->rows[0]['total_weight'];
            $consignment = array(
	       'package'        => $mano_package, // bottle_1 | bottle_2 | bottle_3 | bottle_6 | bottle_12 | other
            	'ToAddress[country_code]' => $musu_country_code,
            	'ToAddress[name]'         => $gkfirstname." ".$gklastname,
            	'ToAddress[business]'     => $gkcompany,
            	'ToAddress[street]'       => $gkaddr,
            	'ToAddress[eps_postcode]' => $gkzip,
            	'ToAddress[town]'         => $gkcity,
            	'ToAddress[email]'        => $gkemail,
            	'ToAddress[phone_number]' => $gkphone,
            	'weight'                  => $weight,
		'custom_id' => str_pad($order['order_id'], 7, '0', STR_PAD_LEFT),//$order['details']['BT']->order_number,
            );
            //print_r($consignment);
            //die;
        }//die;
?>
		<html>
		<body onload="document.getElementById('flespakket-create-consignment').submit();">
            <h4>Sending data to Flespakket ...</h4>
            <form
                action="<?php echo FLESPAKKET_LINK . 'plugin/' . $flespakket_plugin_action . $order_id; ?>?return_url=<?php echo htmlspecialchars(urlencode($return_url)); ?>"
                method="post"
                id="flespakket-create-consignment"
                style="visibility:hidden;"
                >
<?php
        foreach ($consignment as $param => $value)
        {
            echo '<input type="text" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($value) . '" />';
        }
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN POPUP RETURN CLOSE
     */
    if($_GET['action'] == 'return' || $_GET['action'] == 'retour')
    {
      global $db;// = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $order_id       = $_GET['order_id'];
        $timestamp      = $_GET['timestamp'];
        $consignment_id = $_GET['consignment_id'];
        $retour         = ($_GET['action'] == 'retour') ? 1 : 0;
        $tracktrace     = isset($_GET['tracktrace'])?$_GET['tracktrace']:"";
        $postcode       = $_GET['postcode'];

        // save
        /*tep_db_perform('orders_flespakket', array(
            'orders_id'      => $order_id,
            'consignment_id' => $consignment_id,
            'retour'         => $retour,
            'tracktrace'     => $tracktrace,
            'postcode'       => $postcode,
        ));*/
if ($tracktrace) {
        
                $qinsert = "INSERT INTO " . DB_PREFIX . "orders_flespakket SET orders_id='".$order_id."', consignment_id='".$consignment_id."', retour='".$retour."', postcode='".$postcode."', tracktrace = '" . $tracktrace . "'";
		//echo $qinsert; die;
                $db->query( $qinsert );
		//$db->query();

        

        $tracktrace_link = 'https://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl&B=' . $tracktrace . '&P=' . $postcode;
?>
		<html>
		<body onload="updateParentWindow();">
            <h4>Consignment <?php echo $consignment_id; ?> aangemaakt [<a href="<?php echo FLESPAKKET_LINK; ?>plugin/label/<?php echo $consignment_id; ?>">label bekijken</a>]</h4>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Klik hier om terug te keren naar de webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow()
                {
                    if (!window.opener || !window.opener.Flespakket || !window.opener.Flespakket.opencart) {
                        alert('No connection with OpenCart webshop');
                        return;
                    }
                    window.opener.Flespakket.opencart.setConsignmentId('<?php echo $order_id; ?>', '<?php echo $timestamp; ?>', '<?php echo $consignment_id; ?>', '<?php echo $tracktrace_link; ?>', '<?php echo $retour; ?>', 'http://<?php echo $_SERVER["SERVER_NAME"]; ?>/');
                    document.getElementById('close-window').style.display = 'block';
                }
            </script>
        </body>
        </html>
<?php
	}
	else
	{
?>
	<html>
		<body onload="updateParentWindow();">
            <!--<h4>Consignment <?php echo $consignment_id; ?> not created</h4>-->
	    <h4>Aanmaken van het label niet mogelijk; u heeft onvoldoende voorraad van dit type verpakking. Ga naar uw account op www.flespakket.nl om nieuwe voorraad te bestellen.</h4>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Klik hier om terug te keren naar de webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow()
                {
                    if (!window.opener || !window.opener.Flespakket || !window.opener.Flespakket.opencart) {
                        alert('No connection with OpenCart webshop');
                        return;
                    }
                    document.getElementById('close-window').style.display = 'block';
                }
            </script>
        </body>
        </html>
<?php
	}
        exit;
    }

    /*
     *   PLUGIN POPUP PRINT
     */
    if($_GET['action'] == 'print')
    {
        $consignments = $_GET['consignments'];
?>
		<html>
		<body onload="document.getElementById('flespakket-create-pdf').submit();">
            <h4>Sending data to Flespakket ...</h4>
            <form
                action="<?php echo FLESPAKKET_LINK; ?>plugin/genereer-pdf"
                method="post"
                id="flespakket-create-pdf"
                style="visibility:hidden;"
                >
<?php
        echo '<input type="text" name="consignments" value="' . htmlspecialchars($consignments) . '" />';
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN BATCH CREATE
     */
    if($_GET['action'] == 'process')
    {
        //include(DIR_WS_CLASSES . 'order.php');

        $return_url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?action=batchreturn&timestamp=' . $_GET['timestamp'];

        $order_ids = (strpos($_GET['order_ids'], '|') !== false)
        ? explode('|', $_GET['order_ids'])
        : array($_GET['order_ids']);

        $formParams = array();

        foreach($order_ids as $order_id_full)
        {
            $order_id_full_array = explode('.', $order_id_full);
	    $order_id = $order_id_full_array[0];
	    $order_pck = $order_id_full_array[1];
	    $mano_package = '';
		if ($order_pck == 999)
		{
			$mano_package = 'other';
		}
		else
		{
			$mano_package = 'bottle_'.$order_pck;
		}
	    //$order = new order($order_id);
	    $order = getOrderz($order_id/*$_GET['order_id']*/);//echo $order_id."<br/>";
            /*$address = $order->delivery;

            $country_sql = tep_db_query("
SELECT countries_iso_code_2 AS country_code
  FROM " . TABLE_COUNTRIES . "
 WHERE countries_name = '" . $address['country'] . "'
");
            $country = tep_db_fetch_array($country_sql);*/
	    
	    $musu_country_code='';
	    if (strlen($order['shipping_iso_code_2']) > 0) {
		     $musu_country_code = $order['shipping_iso_code_2']; 
	    } else {
		    $musu_country_code = $order['payment_iso_code_2']; 
	    }
		
		
		
	        if (strlen($order['shipping_company']) > 0) {
			$gkcompany = $order['shipping_company']; 
		} else {
			$gkcompany = $order['payment_company']; 
		}
		
		if (strlen($order['shipping_postcode']) > 0) {
			$gkzip = $order['shipping_postcode']; 
		} else {
			$gkzip = $order['payment_postcode']; 
		}
		
		if (strlen($order['shipping_city']) > 0) {
			$gkcity = $order['shipping_city']; 
		} else {
			$gkcity = $order['payment_city']; 
		}
		
		/*if (strlen($order['details']['ST']->email) > 0) {
			$gkemail = $order['details']['ST']->email; 
		} else {
			$gkemail = $order['details']['BT']->email; 
		}*/
		$gkemail = $order['email']; 
		
		if (strlen($order['shipping_firstname']) > 0) {
			$gkfirstname = $order['shipping_firstname']; 
		} else {
			$gkfirstname = $order['payment_firstname']; 
		}
		if (strlen($order['shipping_lastname']) > 0) {
			$gklastname = $order['shipping_lastname']; 
		} else {
			$gklastname = $order['payment_lastname']; 
		}
		if (strlen($order['shipping_address_1']) > 0) {
			$gkaddr = $order['shipping_address_1']; 
		} else {
			$gkaddr = $order['payment_address_1']; 
		}
		/*if (strlen($order['details']['ST']->phone_1) > 0) {
			$gkphone = $order['details']['ST']->phone_1; 
		} else {
			$gkphone = $order['details']['BT']->phone_1; 
		}*/
		$gkphone = $order['telephone'];

            if($musu_country_code=='NL')
	    {
                $street = getAddressComponents($gkaddr);
                $consignment = array(
		  'package'        => $mano_package, // bottle_1 | bottle_2 | bottle_3 | bottle_6 | bottle_12 | other
                    'ToAddress' => array(
                    	'country_code'    => $musu_country_code,
			'name'            => $gkfirstname." ".$gklastname,
			'business'        => $gkcompany,
			'postcode'        => $gkzip,
			'house_number'    => $street['house_number'],
                    	'number_addition' => $street['number_addition'],
                    	'street'          => $street['street'],
			'town'            => $gkcity,
			'email'           => $gkemail,
                    ),
		    'custom_id' => str_pad($order['order_id'], 7, '0', STR_PAD_LEFT),//$order['details']['BT']->order_number,
                );
            }
            else // buitenland
            {
                $weight = 0;
	        global $db;// = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	        $queryz = "SELECT SUM(A.`quantity` * B.`weight`) AS `total_weight` FROM `" . DB_PREFIX . "order_product` AS A, `" . DB_PREFIX . "product` AS B WHERE (A.`order_id` ='".$order['order_id']."') AND (A.`product_id` = B.`product_id`)";
	        $rezultatas = $db->query( $queryz );
	        $weight = $rezultatas->rows[0]['total_weight'];
                $consignment = array(
		  'package'        => $mano_package, // bottle_1 | bottle_2 | bottle_3 | bottle_6 | bottle_12 | other
                    'ToAddress' => array(
			'country_code' => $musu_country_code,
			'name'         => $gkfirstname." ".$gklastname,
			'business'     => $gkcompany,
			'street'       => $gkaddr,
			'eps_postcode' => $gkzip,
			'town'         => $gkcity,
			'email'        => $gkemail,
			'phone_number' => $gkphone,
                    ),
                    'weight' => $weight,
		    'custom_id' => str_pad($order['order_id'], 7, '0', STR_PAD_LEFT),//$order['details']['BT']->order_number,
                );
            }
            $formParams[$order_id] = serialize($consignment);
        }
?>
		<html>
		<body onload="document.getElementById('flespakket-create-consignmentbatch').submit();">
            <h4>Sending data to Flespakket ...</h4>
            <form
                action="<?php echo FLESPAKKET_LINK . 'plugin/verzending-batch'; ?>?return_url=<?php echo htmlspecialchars(urlencode($return_url)); ?>"
                method="post"
                id="flespakket-create-consignmentbatch"
                style="visibility:hidden;"
                >
<?php
        //print_r($formParams);
	foreach ($formParams as $param => $value)
        {
            
	    echo '<input type="text" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($value) . '" />';
        }
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN BATCH RETURN CLOSE
     */
    if($_GET['action'] == 'batchreturn')
    {
        //print_r($_POST);
	$mano_sukurti='';
	$mano_nesukurti='';
	foreach($_POST as $order_id => $serialized_data)
        {
            //echo "--".$order_id."++<br/>";
	    if(!is_numeric($order_id)) continue;

            //$check_sql = tep_db_query("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . tep_db_input($order_id) . "'");

			$query2 = 'SELECT COUNT(order_id) AS count11 FROM `' . DB_PREFIX . 'order` WHERE order_id = "' . $db->escape($order_id) . '"';
			//echo $query2."<br /><br />";
			
			$rezultatas = $db->query( $query2 )->rows[0];
			//echo "---"; print_r($rezultatas); echo "+++";
			//if ($rezultatas == 1) {
	    //echo $rezultatas['count11']."aa<br/><br/>";die;
            if($rezultatas['count11'] == 1)
            {
                $data = unserialize($serialized_data);

                // save
                /*tep_db_perform('orders_flespakket', array(
                    'orders_id'      => $order_id,
                    'consignment_id' => $data['consignment_id'],
                    'retour'         => null,
                    'tracktrace'     => $data['tracktrace'],
                    'postcode'       => $data['postcode'],
                ));*/
                
		if (isset($data['tracktrace'])) {
			if ($mano_sukurti=='')
			{
				$mano_sukurti=$order_id.'['.$data['consignment_id'].']';
			}
			else
			{
				$mano_sukurti.=', '.$order_id.'['.$data['consignment_id'].']';
			}
			$qinsert = "INSERT INTO " . DB_PREFIX . "orders_flespakket SET orders_id='".$order_id."', consignment_id='".$data['consignment_id']."', retour='', postcode='".$data['postcode']."', tracktrace = '" . $data['tracktrace'] . "'";
			//echo "<br/><br/>".$qinsert;//die;
			$db->query( $qinsert );
			//$db->query();
		}
		else
		{
			if ($mano_nesukurti=='')
			{
				$mano_nesukurti=$order_id;
			}
			else
			{
				$mano_nesukurti.=', '.$order_id;
			}
		}
            }
        }
?>
		<html>
		<body onload="updateParentWindow();">
            <?php if ($mano_sukurti!='') { ?><h4>Consignments aangemaakt: <?php echo $mano_sukurti; ?></h4><?php } ?>
	    <?php /*if ($mano_nesukurti!='') { ?><h4>Consignments not created: <?php echo $mano_nesukurti; ?></h4><?php }*/ ?>
	    <?php if ($mano_nesukurti!='') { ?><h4>Aanmaken van het label niet mogelijk [<?php echo $mano_nesukurti; ?>];<br/>u heeft onvoldoende voorraad van dit type verpakking. Ga naar uw account op www.flespakket.nl om nieuwe voorraad te bestellen.</h4><?php } ?>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Klik hier om terug te keren naar de webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow()
                {
                    if (!window.opener || !window.opener.Flespakket || !window.opener.Flespakket.opencart) {
                        alert('No connection with OpenCart webshop');
                        return;
                    }
                    document.getElementById('close-window').style.display = 'block';
                    window.opener.location.reload();
                    <?php if ($mano_nesukurti=='') { ?> window.close(); <?php } ?>
                }
            </script>
        </body>
        </html>
<?php
        exit;
    }
}
?>
