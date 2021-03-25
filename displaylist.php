<?php
global $wpdb;
// Table name
$tablename = $wpdb->prefix."zipcode";
?>
<h2>Import CSV Data</h2>

<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="importfile" >
  <input type="submit" name="butimport" value="Import">
</form>


<h2>Malualy Zipcode Import</h2>

<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>'>
  <input type="text" name="importdata" >
  <input type="submit" name="butsubmit" value="Submit">
</form>

<!-- Record List -->
<!-- <table width='100%' border='1' style='border-collapse: collapse;'>
   <thead>
   <tr>
     <th>S.no</th>
     <th>zipcode</th>
   </tr>
   </thead>
   <tbody>
   <?php
   // Fetch records
   $entriesList = $wpdb->get_results("SELECT * FROM ".$tablename." order by id desc");
   if(count($entriesList) > 0){
     $count = 0;
     foreach($entriesList as $entry){
        $id = $entry->id;
        $zipcode = $entry->zipcode;

        echo "<tr>
        <td>".++$count."</td>
        <td>".$zipcode."</td>
        </tr>
        ";
     }
   }else{
     echo "<tr><td colspan='5'>No record found</td></tr>";
  }
  ?>
  </tbody>
</table> -->
<?php
if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Email_subscription extends WP_List_Table
{
  function __construct()
  {
    global $status, $page;
    parent::__construct(array(

      'singular' => 'hello',

      'plural' => 'hello',

    ));

  }
  function column_default($item, $column_email_address)

  {
    return $item[$column_email_address];
  }
  function column_date_time($item)
  {
    $date_time=$item['date_time'];
    $newDate = date("d-m-Y h:i A", strtotime($date_time));
    return $newDate;
  }
  

  function column_cb($item)

  {
    return sprintf(

      '<input type="checkbox" name="id[]" value="%s" />',

      $item['id']

    );

  }
  function get_columns()

  {
    $columns = array(
     'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
     'zipcode' => __('Zip Code', 'cost_table_example'),
    );
    return $columns;
  }
  function get_sortable_columns()
  {
    $sortable_columns = array(
      'id' => array('id', true),
      'zipcode' => array('zipcode', true)
    );
    return $sortable_columns;

  }
  function get_bulk_actions()

  {
    $actions = array(
      'delete' => 'Delete'
    );
    return $actions;
  }
  function process_bulk_action()

  {
    global $wpdb;
  $table_name = $wpdb->prefix . 'zipcode'; // do not forget about tables prefix
  if ('delete' === $this->current_action()) {
    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
    if (is_array($ids)) $ids = implode(',', $ids);
    if (!empty($ids)) {
      $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
    }

  }
}
function prepare_items()

{
  global $wpdb;
     $table_name = $wpdb->prefix . 'zipcode'; // do not forget about tables prefix
     $per_page = 20; // constant, how much records will be shown per page
     $columns = $this->get_columns();
     $hidden = array();
     $sortable = $this->get_sortable_columns();
     $this->_column_headers = array($columns, $hidden, $sortable);
     $this->process_bulk_action();
     $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

     $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1)* $per_page : 0;

     $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';

     $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

     $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name  ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

     $this->set_pagination_args(array(

            'total_items' => $total_items, // total items defined above

            'per_page' => $per_page, // per page constant defined at top of method

            'total_pages' => ceil($total_items / $per_page) // calculate pages count

        ));

 }

}
function Email_subscription_handler()

{
  global $wpdb;
  $table_name = $wpdb->prefix . 'zipcode';
  $table = new Email_subscription();
  $table->prepare_items();
  $message = '';
  if ('delete' === $table->current_action()) {
    $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'cost_table_example'), count($_REQUEST['id'])) . '</p></div>';

  }
  
  ?>

  <div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2 style="text-align:center">All Zipcode Entries</h2>
    <?php echo $message; ?>
    <form id="persons-table" method="GET">
      <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
      <?php $table->display() ?>
    </form>
  </div>
  <?php

}
function cost_table_example_languages()
{
  load_plugin_textdomain('cost_table_example', false, dirname(plugin_basename(__FILE__)));

}

add_action('init', 'cost_table_example_languages');



// Import CSV
if(isset($_POST["butimport"])){
    
   // Allowed mime types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    
    // Validate whether selected file is a CSV file
    if(!empty($_FILES['importfile']['name']) && in_array($_FILES['importfile']['type'], $csvMimes)){
        
        // If the file is uploaded
        if(is_uploaded_file($_FILES['importfile']['tmp_name'])){
            
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['importfile']['tmp_name'], 'r');

            // Skip the first line
             fgetcsv($csvFile);
           
            
            // Parse data from CSV file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
              
                // Get row data
                $zipcode   = $line[0];

                
                // Check whether member already exists in the database with the same zipcode
                $prevQuery = "SELECT count(*) as count FROM {$tablename} where zipcode='".$zipcode."'";
                
                $prevResult = $wpdb->get_results($prevQuery, OBJECT);
                foreach ($prevResult as  $value) {
                  $result_data = $value->count;
                  if($result_data >= 1)
                {
                  echo "";
                }
                else{
                // Insert member data in the database
                  $wpdb->insert($tablename, array(
                     'zipcode' =>$zipcode
                      ));
                    
                     
               }
               }  
            }
            echo "<h3 style='color: green;'>zipcode imported  Successfully</h3>";

            // Close opened CSV file
            fclose($csvFile);
            
           
        }else{
            echo "<h3 style='color: red;'>Data Submit Not Successfully</h3>";
        }
    }else{
        echo "<h3 style='color: red;'>Please choose csv file to import</h3>";
    }

}

if(isset($_POST["butsubmit"])){
    
           $zipcode_data = $_POST['importdata'];
           $prevQuery = "SELECT count(*) as count FROM {$tablename} where zipcode='".$zipcode_data."'";

                $prevResult = $wpdb->get_results($prevQuery, OBJECT);
                foreach ($prevResult as  $value) {
                  $result_data = $value->count;
                  if($result_data >= 1){
                     echo "<h3 style='color: red;'>Zipcode Alredy Exist</h3>";
                  }
                  else{
                    $wpdb->insert($tablename, array(
                     'zipcode' =>$zipcode_data
                      ));
                    echo "<h3 style='color: green;'>Zipcode Added Successfully</h3>";
                  }
                }               
}


?>

