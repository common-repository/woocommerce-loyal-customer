<?php
/*************************** LOAD THE BASE CLASS *******************************
 ********************************************************************************/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/************************* CREATE A PACKAGE CLASS *****************************
 *******************************************************************************/
class Customer_Oder_Detail_Table extends WP_List_Table {
    
  function column_default($item, $column_name){
        
		switch($column_name){
            case 'cus_id':
			return $item->id;
			break;
			case 'cus_name':
			return $item->fname." ".$item->lname;
			break;
            case 'cus_email':
			return $item->email;  
			case 'order_count':
			return $item->numberoforder;
			break;
			case 'order_color':
			if($item->numberoforder==1)
			  {
				  $item_color	=	"<div style='background:grey;width:20px;height:20px; border-radius: 1em;'></div>";
				  }
				  else if($item->numberoforder >=2 && $item->numberoforder<=4)
				  {
					 $item_color	=	"<div style='background:orange;width:20px;height:20px; border-radius: 1em;'></div>";
					  }
				  else if($item->numberoforder >=5 && $item->numberoforder<=8)
				  {
					  $item_color	=	"<div style='background:yellow;width:20px;height:20px; border-radius: 1em;'></div>";
					  }
				  else if($item->numberoforder >=9 && $item->numberoforder<=12)
				  {
					  $item_color	=	"<div style='background:green;width:20px;height:20px; border-radius: 1em;'></div>";
					  }
				  else if($item->numberoforder >=13)
				  {
					  $item_color	=	"<div style='background:blue;width:20px;height:20px; border-radius: 1em;'></div>";
					  }
			return $item_color;
			break;
				 default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_columns(){
        $columns = array(
            'cus_id'     => 'Customer ID',
            'cus_name'    => 'Customer Name',
            'cus_email'  => 'Customer Email',
			'order_count' => 'Order Count',
			'order_color' => 'Order Color'
        );
        return $columns;
    }
    function get_sortable_columns() {
        $sortable_columns = array(
            'cus_id'     => array('cus_id',false),     //true means it's already sorted
            'order_count'    => array('order_count',false)
        );
        return $sortable_columns;
    }
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
        
       // First, lets decide how many records per page to show

        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);		
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
if(isset($_POST['sort_by_start_date']) || isset($_POST['sort_by_end_date']) ){
		$sort_start_Date = esc_attr($_POST['sort_by_start_date']);
		$sort_end_Date = esc_attr($_POST['sort_by_end_date']);
		
		$sql="
SELECT count(Distinct(ID)) as numberoforder, PM1.meta_value as email, PM2.meta_value as fname,PM3.meta_value as lname, PM4.meta_value as id FROM {$wpdb->prefix}posts AS P INNER JOIN {$wpdb->prefix}postmeta AS PM1 ON P.ID=PM1.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM2 ON P.ID=PM2.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM3 ON P.ID=PM3.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM4 ON P.ID=PM4.post_id WHERE P.post_status ='wc-completed' and PM1.meta_key='_billing_email' AND post_date BETWEEN '{$sort_start_Date}  00:00:00' AND '{$sort_end_Date} 23:59:59' and PM2.meta_key='_billing_first_name' and PM3.meta_key='_billing_last_name' and PM4.meta_key='_customer_user' ".$s. " GROUP BY email order by numberoforder  ".$countorder; 
	
				}else{
		 $sql="
SELECT count(Distinct(ID)) as numberoforder, PM1.meta_value as email, PM2.meta_value as fname,PM3.meta_value as lname, PM4.meta_value as id FROM {$wpdb->prefix}posts AS P INNER JOIN {$wpdb->prefix}postmeta AS PM1 ON P.ID=PM1.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM2 ON P.ID=PM2.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM3 ON P.ID=PM3.post_id INNER JOIN {$wpdb->prefix}postmeta AS PM4 ON P.ID=PM4.post_id WHERE P.post_status ='wc-completed' and PM1.meta_key='_billing_email' and PM2.meta_key='_billing_first_name' and PM3.meta_key='_billing_last_name' and PM4.meta_key='_customer_user' ".$s. " GROUP BY email order by numberoforder  ".$countorder; 
				}
					
		$users_count = $wpdb->get_results($sql);
	        
        $data = $users_count;
       
        $current_page = $this->get_pagenum();
      
		$total_items = count($data);
        
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
       
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
	
        function usort_reorder($a,$b){
            $orderby = (!empty(esc_attr($_REQUEST['orderby']))) ? esc_attr($_REQUEST['orderby']) : 'order_count'; //If no sort, default to title
            $order = (!empty(esc_attr($_REQUEST['order']))) ? esc_attr($_REQUEST['order']) : 'desc'; //If no order, default to asc
            
			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
           
		    return ($order==='desc') ? $result : -$result; //Send final sort direction to usort
        }
						
		function fused_get_all_user_orders($user_id)
		{
			if(!$user_id)return false;
			
			if( $this->woocommerce_version_check() < 2.2 ) 
			{
				$user_order = query_posts(
					array(
						'post_type'   => 'shop_order', 
						'meta_key'    => '_customer_user', 
						'meta_value'  => $user_id,
						'posts_per_page' => -1
						)
					);
			 //getting each order of single user..... where order status = completed 
			$c = 0;
			foreach ($user_order as $customer_order) 
			{
				$order = new WC_Order();
				$order->populate($customer_order);
				$orderdata = (array) $order;
				
				if( $orderdata['status'] == 'completed' )
				{
					$c++;
					}
				}
			 //return counted array 
			return $c;
			}
			else			
			{				
				$user_order = query_posts(
				array(
					'post_type'   => 'shop_order', 
					'meta_key'    => '_customer_user', 
					'meta_value'  => $user_id,
					'posts_per_page' => -1,
					'post_status' => 'wc-completed'
					)
				);

				
				$c = 0;
	
				foreach ($user_order as $customer_order) 
				{
					$order = new WC_Order();
					$order->populate($customer_order);
					$orderdata = (array) $order;
											
					if( $orderdata['post_status'] == 'wc-completed' )
					{
						$c++;
						}
					}
				return $c;
				}
			}
		function woocommerce_version_check() 
		{
			// If get_plugins() isn't available, require it
			if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
			// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file = 'woocommerce.php';
		
		// If the plugin version number is set, return it 
		if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
			
			return (float) $plugin_folder[$plugin_file]['Version'];
	
		} else {
		// Otherwise return null
			return NULL;
		}
	}
}

function render_list_page(){
    //Create an instance of our package class...
    $Cod_ListTable = new Customer_Oder_Detail_Table();
    //Fetch, prepare, sort, and filter our data...
    $Cod_ListTable->prepare_items();
    ?>
    
    <div class="wrap">
  <h2> WooCommerce Loyal Customers </h2>
  <?php /* admin notic ... color code information */ ?>
  <table class="widefat">
    <thead>
      <tr>
      	<th><button class="trs_wclc_emaillist_csv button">Download customers email list</button></th>
        <th><div style='background:grey;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;1 Order</th>
        <th><div style='background:orange;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;2 - 4 Order</th>
        <th><div style='background:yellow;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;5 - 8 Order</th>
        <th><div style='background:green;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;9 - 12 Order</th>
        <th><div style='background:blue;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;13+ Order</th>
        <th style="background:#615555; padding-right: 0px;"> 
          <form action="" method="post">
            <input type="search" name="search_cus_ord" style="height: 2em;">
            <input type="submit" name="search_cus_ord_b" value="Search" class="button">
          </form>
        </th>
      </tr>
    </thead>
  </table>

  <form method="post" action="">
  <div class="dateranges">
	<div class="startdatefield">
		<label>Select Start Date Range</label>
		<input type="date" name="sort_by_start_date">
		</div>
		<div class="enddatefield">
		<label>Select End Date Range</label>
		<input type="date" name="sort_by_end_date">
		</div>
		 <input type="submit" name="search_date_ranges" value="Search" class="datebutton">
	  </div>
	  </form>
	  <style>
	  .dateranges {
    background: #ffffff;
    padding: 10px 10px;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
    margin-top: 10px;
	    display: flex;
}
.enddatefield {
    margin-left: 10px;
}
}
.datebutton {
    margin-left: 10px;
    color: #2271b1;
    border-color: #2271b1;
    background: #f6f7f7;
    vertical-align: top;
	cursor: pointer;
}
	  </style>
           <!-- Now we can render the completed list table -->
            <?php $Cod_ListTable->display() ?>        
    </div>
    <?php
}