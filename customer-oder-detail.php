<?php   
/**
 * Plugin Name: WooCommerce Loyal Customer
 * Plugin URI: https://www.therightsw.com/
 * Description: Woo Commerce Loyal Customer provides wp-admin a way to view the total number of orders received per registered customer in a very user friendly manner with the help of color codes.
 * Version: 2.3
 * Author: The Right Software
 * Author URI: https://therightsw.com/plugin-development/
 * Tested up to: 5.9.1
 * License: GPL2
 */
			function trs_wlc_acitave_plugin(){
	if(!is_plugin_active('woocommerce/woocommerce.php')){
		 echo '<div id="message" class="error fade"><p style="line-height: 150%">';
	
		_e('<strong>WooCommerce Loyalty Customer requires Woocommerce</strong></a> Plugin. 	<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">Click</a> here to download', 'trs_wlc');
	
		echo '</p></div>';
		    deactivate_plugins( plugin_basename( __FILE__ ) );
		 if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
	}
	}

add_action( 'admin_init', 'trs_wlc_acitave_plugin' );
/*
|--------------------------------------------------------------------------
| CHECK CLASS EXISTS OR NOT
|--------------------------------------------------------------------------
*/

if ( !class_exists( 'customer_order' )){
/*
|--------------------------------------------------------------------------
| START PLUGIN CLASS Name CUSTOMER ORDER
|--------------------------------------------------------------------------
*/
	
class Customer_Order_Detail{
	
	function __construct(){		
			add_action('admin_init', array(
                &$this,
                'trs_wc_loyal_customer_admin_init'
            ));
			 add_action('wp_ajax_nopriv_trs_wc_loyal_emaillist_csv', array(
                &$this,
                'trs_wc_loyal_emaillist_csv'
            ));
            add_action('wp_ajax_trs_wc_loyal_emaillist_csv', array(
                &$this,
                'trs_wc_loyal_emaillist_csv'
            ));
	/*
	|--------------------------------------------------------------------------
	| APPLY ACTIONS & FILTERS IS WOOCOMMERCE IS ACTIVE
	|--------------------------------------------------------------------------
	*/
	/* woocommerce dependency check */
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		/********** action to menu  *********/
		add_action('admin_menu',  array(&$this,'woo_shop_order_details_menu'), 10);
	}	
}				

/*
|--------------------------------------------------------------------------
| START PLUGIN FUNCTIONS
|--------------------------------------------------------------------------
*/
/* Adding Submenu page in woocommerce meun */
function woo_shop_order_details_menu() { 
	add_submenu_page( 'woocommerce', __('Loyal Customers', ''), __('Loyal Customers', ''), 'manage_options', 'cust-orders', 
	array(&$this,'list_ord_page'));
}
/*----------Admin Js---------*/
  function trs_wc_loyal_customer_admin_init(){
			wp_enqueue_script('trs_wc_loyal_customer_admin_custom_js',plugins_url('js/custom-admin.js', __FILE__),false,'2.1',true);

  }
/*--------email list csv------------*/
function trs_wc_loyal_emaillist_csv(){
          header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.site_url().'/wp-content/uploads/'.md5(time()).'_report.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Email', 'Name'));

// fetch the data
 global $wpdb;
  $code_sort_by = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
		$countorder	=	"desc";
		if($code_sort_by == 'asc'){
				$countorder	= 'asc';
			}
			elseif($code_sort_by == 'desc')
			{
				$countorder	= 'desc';
			}
			
						$s="";
					if(isset($_POST['search_cus_ord'])){
						$searchs=($_POST['search_cus_ord']);
				$search=explode(" ",$searchs);
						 $s= " and (( PM1.meta_value LIKE '%".$search[0]."%' OR PM2.meta_value LIKE '%".$search[0]."%'";
						if(isset($search[1]))
						$s .=" OR PM3.meta_value LIKE '%".$search[1]."%')) ";					 
						else 
						$s .=" OR PM3.meta_value LIKE '%".$search[0]."%')) ";
				
						}						

  $rows = $wpdb->get_results("SELECT count(Distinct(ID)) as numberoforder, PM1.meta_value as email, PM2.meta_value as fname,PM3.meta_value as lname, PM4.meta_value as id FROM {$wpdb->prefix}posts AS P INNER JOIN {$wpdb->prefix}postmeta AS PM1 ON P.ID=PM1.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM2 ON P.ID=PM2.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM3 ON P.ID=PM3.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM4 ON P.ID=PM4.post_id WHERE P.post_status ='wc-completed' and PM1.meta_key='_billing_email' and PM2.meta_key='_billing_first_name' and PM3.meta_key='_billing_last_name' and PM4.meta_key='_customer_user' ".$s. " GROUP BY email order by numberoforder  ".$countorder);
 
foreach ( $rows as $u ) {
		$row = array();
		$row[0] = '"'.$u->email.'"';
		$row[1] = '"'.$u->fname.' '.$u->lname.'"';
		$data_rows[] = $row;
		
	}

foreach ( $data_rows as $data_row ) {
		fputcsv( $output, $data_row );
	}


  // output headers so that the file is downloaded rather than displayed
  header("Content-type: text/csv");
  header("Content-disposition: attachment; filename = ".site_url()."/wp-content/uploads/".md5(time())."_report.csv");
  $getcwd = substr(getcwd(), 0, -8);
  readfile($getcwd."wp-content/uploads/".md5(time())."_report.csv");
            
        }
	
/* including main plugin file */
function list_ord_page() {
	 //include('admin/list-cus.php');
	 include('admin/customer.php');
	 render_list_page();
	 $plugin_data = get_plugin_data(plugin_dir_path(__FILE__ )."customer-oder-detail.php");
	 ?>
     <script type="text/javascript">
var ajaxurl = '<?php  echo admin_url('admin-ajax.php');?>';
</script>
     <style>
	 	.rating span:before {
   content: "\2605";
   position: absolute;
   text-decoration:underline;
   
}
	 </style>
	<?php echo "<div><div style=\"float:left;\"> Developed by <a href=\"".$plugin_data["PluginURI"]."\" style=\"text-decoration: none;\" target=\"_blank\" >The Right Software</a><span>|</span><a href=\"".$plugin_data["AuthorURI"]."\" style=\"text-decoration: none;\" target=\"_blank\"> Contact Support</a><span> |</span><a href=\"#\" style=\"text-decoration: none;\" target=\"_blank\"> Donate </a>to this plugin. </div><div style=\"float:right;\">Give us a Reveiw <a  href=\"https://wordpress.org/support/view/plugin-reviews/woocommerce-loyal-customer?filter=5\" style=\"text-decoration: none;\"  class='rating' target=\"_blank\">
<span>☆<span>☆</span><span>☆</span><span>☆</span><span>☆</span></a> | WLC ".$plugin_data["Version"]."</div></div>";  
	 
}
	} 
/*Class End */	
	
} /* IF Condition End */

$cod_customer_order = new Customer_Order_Detail();	