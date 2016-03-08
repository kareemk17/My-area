<?php
/*
Plugin Name: woocommerce frontend add product
Description: This plugin let your vendors and clients to add their products from frontend finally :D
Author: Kareem Mortada
Version: 1.0.0
*/

global $title,$description,$price,$stock_qu,$product_cat_name;

wp_enqueue_style( 'add_product_style', plugins_url( 'woocommerce_frontend_add_product.css', __FILE__ ) );

function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}

/*
* This function add feature image with new product
*/
function insert_product_images($post_id) {
if ( !empty( $_FILES ) ) {
   require_once(ABSPATH . 'wp-admin/includes/admin.php');
   $id = media_handle_upload('image', $post_id); //post id of Client Files page
   unset($_FILES);
   if ( is_wp_error($id) ) {
       $errors['upload_error'] = $id;
       $id = false;
   }
}
return $id ;
}

/*
 * category validation
 */
function validation_of_cat($categories,$pro,$c) {
  foreach ($categories as $key) {
    if ($key->name == $c){
      wp_set_object_terms($pro, $c,'product_cat');
    }
  }
}
function show_products($id){
  $product_link = $url = get_permalink( $id );
  echo '<div class="page-container woocommerce main-woocommerce-container">';
  echo '<div class="woocommerce-message"><a href="'. $product_link .'" class="button wc-forward">View Product</a>Your product "'. get_the_title( $id ) .'" has been added!</div></div>';
}

/*
 * Here we go :D
 */
 function vendor_add_products() {
   /*
    * Get woocommerce taxonomy to can list category
    */
 $args = array(
        'taxonomy'     => 'product_cat',
        'orderby'      => 'name',
        'show_count'   => 0,   // 1 for yes, 0 for no
        'pad_counts'   => 0,   // 1 for yes, 0 for no
        'hierarchical' => 1,   // 1 for yes, 0 for no
        'title_li'     => '',
        'hide_empty'   => 0
 );
 $all_categories = get_categories( $args );

/*
 * Validate the product data
 */
if (isset($_POST["submit"])) {
  $check = 1;
       if (empty($_POST["title"])) {
         $titleErr = '<script>document.getElementById("title").setAttribute("class","error_falid");</script>';
       } else {
         $title = test_input($_POST["title"]);
         $title_is_right = '<script>document.getElementById("title").setAttribute("class","correct");</script>';
       }

       if (empty($_POST["price"])) {
         $priceErr = '<script>document.getElementById("price").setAttribute("class","error_falid");</script>';
       } else {
         $price = test_input($_POST["price"]);
         $price_is_right = '<script>document.getElementById("price").setAttribute("class","correct");</script>';
       }

       if (empty($_POST["stock_qu"])) {
         $stockErr = '<script>document.getElementById("stock_qu").setAttribute("class","error_falid");</script>';
       } else {
         $stock_qu = intval(test_input($_POST["stock_qu"]));
         $stock_is_right = '<script>document.getElementById("stock_qu").setAttribute("class","correct");</script>';
       }
       if (empty($_POST["description"])) {
         $description = "";
       } else {
         $description = $_POST["description"];
       }
       if (empty($_POST["product_category"])){
         $product_category = '';
       }
       else {
         $product_cat_name = $_POST["product_category"];
       }
    }
  if(isset($title) && isset($price) && isset($stock_qu)){
    $u_id = get_current_user_id();
    $post = array(
    'post_title' => wp_strip_all_tags( $title ),
    'post_content' => $description,
    'post_category' => $product_cat_name,  // Usable for custom taxonomies too
    'post_status' => 'publish',		// Choose: publish, preview, future, etc.
    'post_author'   => $u_id ,
    'post_type' => 'product'  // Use a custom post type if you want to
    );
    $product_ID = wp_insert_post($post);  // http://codex.wordpress.org/Function_Reference/wp_insert_post
     if ( $product_ID ){
           validation_of_cat($all_categories,$product_ID, $product_cat_name);
          $image = insert_product_images($product_ID);
          add_post_meta($product_ID, '_regular_price', $price );
          add_post_meta($product_ID, '_price', $price );
          add_post_meta($product_ID, '_stock_status', 'instock' );
          add_post_meta($product_ID, '_visibility', 'visible' );
          add_post_meta($product_ID, '_manage_stock', "yes" );
          add_post_meta($product_ID, '_stock', $stock_qu);
          add_post_meta($product_ID, '_thumbnail_id', $image );
          show_products($product_ID);
          echo $price ." " . $stock_qu;
          }
    }

echo '<div class="col-xs-12 col-md-12 add-product">';
echo '<form id="custom-post-type" name="custom-post-type" method="post" action="'. htmlspecialchars($_SERVER["PHP_SELF"]) .'" enctype="multipart/form-data" >';
echo '<input type="text" id="title" value="' . $title .'" tabindex="1" name="title" placeholder="Porduct Title *" /></br>';
echo (isset($title)) ? $title_is_right: $titleErr;
echo '<input type="number" id="price" value="' . $price .'" tabindex="2" name="price" placeholder="Prodect Price *" /></br>';
echo (isset($price)) ? $price_is_right : $priceErr;
echo '<input type="number" id="stock_qu" value="' . $stock_qu .'" tabindex="3" name="stock_qu" placeholder="Product Quantity *"/></br>';
echo (isset($stock_qu)) ? $stock_is_right : $stockErr;
echo '<textarea id="description" tabindex="4" name="description"  placeholder="Product Description">';
echo (isset($description)) ? htmlspecialchars($description) : '';
echo '</textarea></br>';
echo '<div style="margin-top: 20px;">';
echo '<select name="product_category" placeholder="category"><option selected>Select Category</option>';
foreach ($all_categories as $cat) {
 echo '<option value="' . $cat->name . '">' . $cat->name . '</option>';
}
echo '</select>';
echo '</div>';
echo '<input type="file" name="image" id="image" size="50" accept="image/*"></br>';
echo ($check == 1) ? '<div class="Err">You have to set image for your product</div>': '<div>';
echo '<div id="submit">';
echo '<input type="submit" value="Publish" tabindex="6" id="submit" name="submit" /></br>';
echo '</div>';

echo '</form>';
echo '</div>';
}

add_shortcode('wfad','vendor_add_products');

?>
