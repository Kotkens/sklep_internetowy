<?php
/* Template Name: Sprzedawaj */
if(!defined('ABSPATH')) exit;

// Użytkownik (potrzebny do walidacji POST) – jeśli nie zalogowany później pokażemy komunikat
$current_user = is_user_logged_in() ? wp_get_current_user() : null;

// Komunikaty (gromadzone przed renderem)
$messages = [];

// Funkcja pomocnicza do uploadu obrazka wyróżniającego
if(!function_exists('preomar_validate_image_requirements')){
function preomar_validate_image_requirements($file_field,&$messages,$required=true){
  if(empty($_FILES[$file_field]['name'])){
    if($required){
      $messages[]=['type'=>'error','text'=>'Zdjęcie główne wymagane.'];
      return false;
    }
    return true; // nie wymagane i brak
  }
  $allowed_mimes = [ 'image/jpeg','image/png','image/webp' ];
  $max_file_size_mb = 10;
  $min_width  = 500;
  $min_height = 500;
  $max_ratio_deviation = 3;
  $f = $_FILES[$file_field];
  if(!empty($f['error']) && $f['error'] !== UPLOAD_ERR_OK){
    $messages[]=['type'=>'error','text'=>'Błąd przesyłania pliku (kod '.$f['error'].').'];
    return false;
  }
  $tmp = $f['tmp_name'];
  $img_info = @getimagesize($tmp);
  if(!$img_info){
    $messages[]=['type'=>'error','text'=>'Nieprawidłowy obraz – nie można odczytać danych.'];
    return false;
  }
  list($w,$h) = $img_info; $mime = $img_info['mime'] ?? '';
  if(!in_array($mime,$allowed_mimes,true)){
    $messages[]=['type'=>'error','text'=>'Niedozwolony format. Dozwolone: JPG, PNG, WebP.'];
    return false;
  }
  $size_bytes = isset($f['size']) ? (int)$f['size'] : filesize($tmp);
  if($size_bytes > $max_file_size_mb * 1024 * 1024){
    $messages[]=['type'=>'error','text'=>'Plik jest za duży (max '.$max_file_size_mb.' MB).'];
    return false;
  }
  if($w < $min_width || $h < $min_height){
    $messages[]=['type'=>'error','text'=>'Zdjęcie jest za małe. Minimum '.$min_width.'x'.$min_height.' px.'];
    return false;
  }
  $ratio = $w >= $h ? ($w / max(1,$h)) : ($h / max(1,$w));
  if($ratio > $max_ratio_deviation){
    $messages[]=['type'=>'error','text'=>'Zbyt panoramiczne proporcje. Użyj bardziej standardowych wymiarów.'];
    return false;
  }
  return true;
}}

if(!function_exists('preomar_upload_featured_image')){
function preomar_upload_featured_image($file_field,$post_id,&$messages,$required=true){
  if(empty($_FILES[$file_field]['name'])) return 0; // brak uploadu
  // zakładamy wcześniejszą walidację; jeśli chcesz włączyć dodatkową – odkomentuj poniższą linię
  // if(!preomar_validate_image_requirements($file_field,$messages,$required)) return 0;
  require_once ABSPATH.'wp-admin/includes/file.php';
  require_once ABSPATH.'wp-admin/includes/media.php';
  require_once ABSPATH.'wp-admin/includes/image.php';
  $overrides=['test_form'=>false];
  $file = wp_handle_upload($_FILES[$file_field],$overrides);
  if(isset($file['error'])){
    $messages[]=['type'=>'error','text'=>'Błąd uploadu obrazka: '.$file['error']];
    return 0;
  }
  $attachment = [
    'post_mime_type'=>$file['type'],
    'post_title'=>sanitize_file_name(basename($file['file'])),
    'post_content'=>'',
    'post_status'=>'inherit'
  ];
  $attach_id = wp_insert_attachment($attachment,$file['file'],$post_id);
  if(!is_wp_error($attach_id)){
    $attach_data = wp_generate_attachment_metadata($attach_id,$file['file']);
    wp_update_attachment_metadata($attach_id,$attach_data);
    set_post_thumbnail($post_id,$attach_id);
    return $attach_id;
  } else {
    $messages[]=['type'=>'error','text'=>'Nie udało się zapisać załącznika.'];
  }
  return 0;
}}

// Dodawanie nowego produktu
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['preomar_new_product_nonce']) && wp_verify_nonce($_POST['preomar_new_product_nonce'],'preomar_new_product')){
  if(!is_user_logged_in()){
    $messages[]=['type'=>'error','text'=>'Musisz być zalogowany.'];
  } else {
  // Pozwalamy każdemu zalogowanemu użytkownikowi dodać produkt jako "oczekujący na zatwierdzenie" (pending)
    $title = sanitize_text_field($_POST['product_title']??'');
  $price = sanitize_text_field($_POST['product_price']??'');
  $sale_price = sanitize_text_field($_POST['product_sale_price']??'');
  $sale_from  = sanitize_text_field($_POST['product_sale_from']??'');
  $sale_to    = sanitize_text_field($_POST['product_sale_to']??'');
    $desc  = wp_kses_post($_POST['product_desc']??'');
  $cats  = isset($_POST['product_cat']) ? array_map('intval', (array)$_POST['product_cat']) : [];
  $qty   = isset($_POST['product_qty']) ? (int)$_POST['product_qty'] : 0;
    $errors=[];
    if(!$title) $errors[]='Tytuł wymagany';
  if($price==='' || !is_numeric($price)) {
    $errors[]='Cena musi być liczbą';
  } else {
    if($price <= 0) $errors[]='Cena musi być większa od zera';
    if($price > 10000) $errors[]='Cena nie może przekraczać 10000 PLN';
  }
  if($sale_price !== ''){
    if(!is_numeric($sale_price)) {
      $errors[]='Cena promocyjna musi być liczbą';
    } else {
      if($sale_price <= 0) $errors[]='Cena promocyjna musi być większa od zera';
      if(is_numeric($price) && $sale_price >= $price) $errors[]='Cena promocyjna musi być mniejsza niż regularna';
    }
  }
  $sale_from_ts = $sale_from ? strtotime($sale_from.' 00:00:00') : 0;
  $sale_to_ts   = $sale_to ? strtotime($sale_to.' 23:59:59') : 0;
  if($sale_from_ts && $sale_to_ts && $sale_from_ts > $sale_to_ts) $errors[]='Zakres dat promocji jest nieprawidłowy (data OD musi być wcześniejsza niż data DO)';
  if(empty($cats)) $errors[]='Wybierz kategorię';
  if($qty < 1) $errors[]='Ilość musi być >= 1';
    // Walidacja zdjęcia przed utworzeniem (musi przejść) – dodajemy do listy błędów jeśli niepoprawne
    if(!preomar_validate_image_requirements('product_image',$messages,true)){
      $errors[] = 'Zdjęcie nie spełnia wymagań.'; // komunikat szczegółowy już dodany
    }
    if(empty($errors)) {
      $post_id = wp_insert_post([
        'post_title'=>$title,
        'post_content'=>$desc,
        'post_type'=>'product',
        'post_status'=>'pending',
        'post_author'=>$current_user->ID
      ]);
  if($post_id && !is_wp_error($post_id)) {
  update_post_meta($post_id,'_regular_price',$price);
  // Promocja
  if($sale_price !== '' && is_numeric($sale_price) && $sale_price < $price){
    update_post_meta($post_id,'_sale_price',$sale_price);
    if($sale_from_ts) update_post_meta($post_id,'_sale_price_dates_from',$sale_from_ts);
    if($sale_to_ts)   update_post_meta($post_id,'_sale_price_dates_to',$sale_to_ts);
    // Cena aktywna (sprawdzamy czy dziś w zakresie jeśli ustawiono daty)
    $now=time();
    $active = true;
    if($sale_from_ts && $now < $sale_from_ts) $active=false;
    if($sale_to_ts && $now > $sale_to_ts) $active=false;
    update_post_meta($post_id,'_price',$active ? $sale_price : $price);
  } else {
    delete_post_meta($post_id,'_sale_price');
    delete_post_meta($post_id,'_sale_price_dates_from');
    delete_post_meta($post_id,'_sale_price_dates_to');
    update_post_meta($post_id,'_price',$price);
  }
  update_post_meta($post_id,'_manage_stock','yes');
  update_post_meta($post_id,'_stock',$qty);
  update_post_meta($post_id,'_stock_status', $qty>0 ? 'instock':'outofstock');
  update_post_meta($post_id,'_backorders','no');
        if($cats) wp_set_object_terms($post_id,$cats,'product_cat');
        preomar_upload_featured_image('product_image',$post_id,$messages,true);
        // Powiadom admina o nowym produkcie do moderacji
        $admin_email = get_option('admin_email');
        if($admin_email){
          $edit_link = admin_url('post.php?post='.$post_id.'&action=edit');
          wp_mail($admin_email,'Nowy produkt oczekuje na zatwierdzenie','ID: #'.$post_id."\nTytuł: ".$title."\nEdytuj: ".$edit_link);
        }
        $messages[]=['type'=>'success','text'=>'Produkt zapisany i czeka na zatwierdzenie (#'.$post_id.').'];
      } else {
        $messages[]=['type'=>'error','text'=>'Błąd podczas tworzenia produktu'];
      }
    } else { foreach($errors as $e) $messages[]=['type'=>'error','text'=>$e]; }
  }
}

// Aktualizacja istniejącego produktu
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['preomar_edit_product_nonce']) && wp_verify_nonce($_POST['preomar_edit_product_nonce'],'preomar_edit_product')){
  if(!is_user_logged_in()){
    $messages[]=['type'=>'error','text'=>'Sesja wygasła – zaloguj się ponownie.'];
  } else {
  $edit_id = intval($_POST['edit_product_id']??0);
  $post = $edit_id ? get_post($edit_id) : null;
  if(!$post || $post->post_type!=='product' || intval($post->post_author)!==$current_user->ID){
    $messages[]=['type'=>'error','text'=>'Nieprawidłowy produkt do edycji.'];
  } else {
    $title = sanitize_text_field($_POST['edit_product_title']??'');
  $price = sanitize_text_field($_POST['edit_product_price']??'');
  $sale_price = sanitize_text_field($_POST['edit_product_sale_price']??'');
  $sale_from  = sanitize_text_field($_POST['edit_product_sale_from']??'');
  $sale_to    = sanitize_text_field($_POST['edit_product_sale_to']??'');
    $desc  = wp_kses_post($_POST['edit_product_desc']??'');
  $cats  = isset($_POST['edit_product_cat']) ? array_map('intval', (array)$_POST['edit_product_cat']) : [];
  $qty   = isset($_POST['edit_product_qty']) ? (int)$_POST['edit_product_qty'] : 0;
    $errors=[];
    if(!$title) $errors[]='Tytuł wymagany (edycja)';
  if($price==='' || !is_numeric($price)) {
    $errors[]='Cena musi być liczbą (edycja)';
  } else {
    if($price <= 0) $errors[]='Cena musi być większa od zera (edycja)';
    if($price > 10000) $errors[]='Cena nie może przekraczać 10000 PLN (edycja)';
  }
  if($sale_price !== ''){
    if(!is_numeric($sale_price)) {
      $errors[]='Cena promocyjna musi być liczbą (edycja)';
    } else {
      if($sale_price <= 0) $errors[]='Cena promocyjna musi być większa od zera (edycja)';
      if(is_numeric($price) && $sale_price >= $price) $errors[]='Cena promocyjna musi być mniejsza niż regularna (edycja)';
    }
  }
  $sale_from_ts = $sale_from ? strtotime($sale_from.' 00:00:00') : 0;
  $sale_to_ts   = $sale_to ? strtotime($sale_to.' 23:59:59') : 0;
  if($sale_from_ts && $sale_to_ts && $sale_from_ts > $sale_to_ts) $errors[]='Zakres dat promocji jest nieprawidłowy (data OD musi być wcześniejsza niż data DO) (edycja)';
  if(empty($cats)) $errors[]='Wybierz kategorię (edycja)';
  if($qty < 1) $errors[]='Ilość musi być >= 1 (edycja)';
    if(!preomar_validate_image_requirements('edit_product_image',$messages,false)){
      $errors[] = 'Nowe zdjęcie (podmiana) nie spełnia wymagań.'; // szczegóły już w messages
    }
    if(empty($errors)){
      wp_update_post([
        'ID'=>$edit_id,
        'post_title'=>$title,
        'post_content'=>$desc
      ]);
  update_post_meta($edit_id,'_regular_price',$price);
  if($sale_price !== '' && is_numeric($sale_price) && $sale_price < $price){
    update_post_meta($edit_id,'_sale_price',$sale_price);
    if($sale_from_ts) update_post_meta($edit_id,'_sale_price_dates_from',$sale_from_ts);
    else delete_post_meta($edit_id,'_sale_price_dates_from');
    if($sale_to_ts)   update_post_meta($edit_id,'_sale_price_dates_to',$sale_to_ts);
    else delete_post_meta($edit_id,'_sale_price_dates_to');
    $now=time();
    $active=true;
    if($sale_from_ts && $now < $sale_from_ts) $active=false;
    if($sale_to_ts && $now > $sale_to_ts) $active=false;
    update_post_meta($edit_id,'_price',$active ? $sale_price : $price);
  } else {
    delete_post_meta($edit_id,'_sale_price');
    delete_post_meta($edit_id,'_sale_price_dates_from');
    delete_post_meta($edit_id,'_sale_price_dates_to');
    update_post_meta($edit_id,'_price',$price);
  }
  update_post_meta($edit_id,'_manage_stock','yes');
  update_post_meta($edit_id,'_stock',$qty);
  update_post_meta($edit_id,'_stock_status', $qty>0 ? 'instock':'outofstock');
  update_post_meta($edit_id,'_backorders','no');
      if($cats) wp_set_object_terms($edit_id,$cats,'product_cat');
      if(!empty($_FILES['edit_product_image']['name'])){
        preomar_upload_featured_image('edit_product_image',$edit_id,$messages,false);
      }
      $messages[]=['type'=>'success','text'=>'Produkt zaktualizowany (#'.$edit_id.').'];
      // Przekierowanie aby uniknąć ponownego POST po odświeżeniu
      wp_safe_redirect( add_query_arg('updated','1', remove_query_arg(['edit']) ) );
      exit;
    } else { foreach($errors as $e) $messages[]=['type'=>'error','text'=>$e]; }
  }}
}

// Usuwanie (przeniesienie do kosza) produktu użytkownika
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['preomar_delete_product_nonce']) && wp_verify_nonce($_POST['preomar_delete_product_nonce'],'preomar_delete_product')){
  if(!is_user_logged_in()){
    $messages[]=['type'=>'error','text'=>'Sesja wygasła – zaloguj się ponownie.'];
  } else {
  $del_id = intval($_POST['delete_product_id']??0);
  $post = $del_id ? get_post($del_id) : null;
  if(!$post || $post->post_type!=='product' || intval($post->post_author)!==$current_user->ID){
    $messages[]=['type'=>'error','text'=>'Nie można usunąć wskazanego produktu.'];
  } else {
    $trashed = wp_trash_post($del_id);
    if($trashed){
      $messages[]=['type'=>'success','text'=>'Produkt przeniesiony do kosza (#'.$del_id.').'];
      // Redirect to avoid resubmission
      wp_safe_redirect( remove_query_arg(['edit']) );
      exit;
    } else {
      $messages[]=['type'=>'error','text'=>'Nie udało się przenieść produktu do kosza.'];
    }
  }}
}

// Jeśli wybrano produkt do edycji przez GET
$edit_product = null;
if(isset($_GET['edit'])){
  $edit_id = intval($_GET['edit']);
  $p = get_post($edit_id);
  if($p && $p->post_type==='product' && intval($p->post_author)===$current_user->ID){
    $edit_product = $p;
  }
}
// Kategorie (pobieramy teraz by móc z nich korzystać w formularzu)
$categories = get_terms(['taxonomy'=>'product_cat','parent'=>0,'hide_empty'=>false]);

// Dopiero teraz ładujemy header (nie było żadnego HTML przed ewentualnymi redirectami)
get_header();
if(!is_user_logged_in()) { echo '<div class="sell-auth-box">';
 echo '<h1>Musisz być zalogowany</h1>';
 echo '<p>Zaloguj się aby wystawić przedmiot.</p>';
 echo '<a class="sell-btn-primary" href="'.esc_url( home_url('/moje-konto/') ).'">Zaloguj się</a>';
 echo '</div>'; get_footer(); return; }
?>
<div class="sell-wrapper">
  <h1 class="sell-title">Wystaw przedmiot</h1>
  <?php if($messages): ?>
  <div class="sell-messages">
    <?php foreach($messages as $m): $ok=$m['type']==='success'; ?>
      <div class="sell-message <?php echo $ok? 'is-ok':'is-error'; ?>"><?php echo esc_html($m['text']); ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="sell-form">
    <?php wp_nonce_field('preomar_new_product','preomar_new_product_nonce'); ?>
    <div class="sell-grid">
      <div class="field field--full">
        <label>Tytuł *</label>
        <input type="text" name="product_title" required>
      </div>
      <div class="field">
        <label>Cena (PLN) *</label>
        <input type="number" min="0" max="10000" step="0.01" name="product_price" required>
      </div>
      <div class="field">
        <label>Cena promocyjna</label>
        <input type="number" min="0" max="10000" step="0.01" name="product_sale_price" class="is-sale">
        <div class="field-note">(opcjonalnie – niższa niż regularna)</div>
      </div>
      <div class="field">
        <label>Promocja od</label>
        <input type="date" name="product_sale_from" class="is-sale">
      </div>
      <div class="field">
        <label>Promocja do</label>
        <input type="date" name="product_sale_to" class="is-sale">
      </div>
      <div class="field">
        <label>Ilość sztuk *</label>
        <input type="number" min="1" step="1" name="product_qty" value="1" required>
        <div class="field-note">Kupujący nie będzie mógł dodać do koszyka więcej niż dostępna ilość.</div>
      </div>
      <div class="field">
        <label>Kategoria *</label>
        <select name="product_cat[]" required>
          <option value="">-- wybierz --</option>
          <?php foreach($categories as $cat): ?>
            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Zdjęcie główne *</label>
        <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" required class="file-input">
        <div class="field-note long">Wymagania: JPG / PNG / WebP, min. 500x500 px, maks. 10 MB, brak ekstremalnych panoram (proporcje &lt; 3:1). Zalecane jasne tło i produkt wypełniający kadr.</div>
      </div>
      <div class="field field--full">
        <label>Opis</label>
        <textarea name="product_desc" rows="6"></textarea>
      </div>
    </div>
    <div class="sell-actions">
      <button type="submit" class="sell-btn-primary">Zapisz produkt</button>
      <a class="sell-link" href="<?php echo esc_url( home_url('/ustawienia-konta/') ); ?>">Powrót do ustawień konta</a>
    </div>
  </form>
  <p class="sell-info">Po zapisaniu produkt trafi do moderacji. Po zatwierdzeniu pojawi się w katalogu.</p>

  <?php if($edit_product): ?>
    <?php 
      $edit_price = get_post_meta($edit_product->ID,'_regular_price',true); 
      $edit_terms = wp_get_post_terms($edit_product->ID,'product_cat',['fields'=>'ids']);
      $thumb_id = get_post_thumbnail_id($edit_product->ID);
      $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id,'thumbnail') : '';
    ?>
    <div id="edit-product" class="sell-edit-block">
      <h2 class="sell-subtitle">Edytuj produkt: <?php echo esc_html($edit_product->post_title); ?></h2>
      <form method="post" enctype="multipart/form-data" class="sell-form sell-form--edit">
        <?php wp_nonce_field('preomar_edit_product','preomar_edit_product_nonce'); ?>
        <input type="hidden" name="edit_product_id" value="<?php echo esc_attr($edit_product->ID); ?>">
        <div class="sell-grid">
          <div class="field field--full"><label>Tytuł *</label><input type="text" name="edit_product_title" required value="<?php echo esc_attr($edit_product->post_title); ?>"></div>
          <div class="field"><label>Cena (PLN) *</label><input type="number" min="0" max="10000" step="0.01" name="edit_product_price" required value="<?php echo esc_attr($edit_price); ?>"></div>
          <?php 
            $edit_sale_price = get_post_meta($edit_product->ID,'_sale_price',true);
            $edit_sale_from  = get_post_meta($edit_product->ID,'_sale_price_dates_from',true);
            $edit_sale_to    = get_post_meta($edit_product->ID,'_sale_price_dates_to',true);
            $edit_sale_from_fmt = $edit_sale_from ? date('Y-m-d', (int)$edit_sale_from) : '';
            $edit_sale_to_fmt   = $edit_sale_to   ? date('Y-m-d', (int)$edit_sale_to) : '';
          ?>
          <div class="field"><label>Cena promocyjna</label><input type="number" min="0" max="10000" step="0.01" name="edit_product_sale_price" value="<?php echo esc_attr($edit_sale_price); ?>" class="is-sale"></div>
          <div class="field"><label>Promocja od</label><input type="date" name="edit_product_sale_from" value="<?php echo esc_attr($edit_sale_from_fmt); ?>" class="is-sale"></div>
          <div class="field"><label>Promocja do</label><input type="date" name="edit_product_sale_to" value="<?php echo esc_attr($edit_sale_to_fmt); ?>" class="is-sale"></div>
          <?php $edit_stock = (int)get_post_meta($edit_product->ID,'_stock',true); if($edit_stock<1) $edit_stock=1; ?>
          <div class="field"><label>Ilość sztuk *</label><input type="number" min="1" step="1" name="edit_product_qty" required value="<?php echo esc_attr($edit_stock); ?>"></div>
          <div class="field"><label>Kategoria *</label><select name="edit_product_cat[]" required>
              <option value="">-- wybierz --</option>
              <?php foreach($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected( in_array($cat->term_id,$edit_terms,true) ); ?>><?php echo esc_html($cat->name); ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="field"><label>Zdjęcie (podmień)</label><input type="file" name="edit_product_image" accept="image/jpeg,image/png,image/webp" class="file-input">
            <div class="field-note">Wymagania jak wyżej: min. 500x500 px, do 10 MB, format JPG/PNG/WebP.</div>
            <?php if($thumb_url): ?>
              <div class="thumb-current">
                <img src="<?php echo esc_url($thumb_url); ?>" alt="miniatura">
                <span>Aktualna miniatura</span>
              </div>
            <?php endif; ?>
          </div>
          <div class="field field--full"><label>Opis</label><textarea name="edit_product_desc" rows="6"><?php echo esc_textarea($edit_product->post_content); ?></textarea></div>
        </div>
        <div class="sell-actions">
          <button type="submit" class="sell-btn-alt">Zapisz zmiany</button>
          <a href="<?php echo esc_url( remove_query_arg('edit') ); ?>" class="sell-link">Anuluj edycję</a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <?php
  // Lista produktów użytkownika
  $user_products = get_posts([
      'post_type'=>'product',
      'post_status'=>['pending','publish','draft'],
      'author'=>$current_user->ID,
      'posts_per_page'=>50,
      'orderby'=>'date','order'=>'DESC'
  ]);
  if($user_products): ?>
  <div class="user-products">
    <h2 class="sell-subtitle">Twoje produkty</h2>
    <div class="user-products-table-wrap">
      <table class="user-products-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Miniatura</th>
            <th>Tytuł</th>
            <th>Cena</th>
            <th>Status</th>
            <th>Data</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($user_products as $prod):
              $p_price = get_post_meta($prod->ID,'_regular_price',true);
              $thumb = get_the_post_thumbnail($prod->ID,'thumbnail',['class'=>'user-prod-thumb']);
          ?>
          <tr>
            <td data-label="ID">#<?php echo esc_html($prod->ID); ?></td>
            <td data-label="Miniatura"><?php echo $thumb ?: '<span class="no-thumb">brak</span>'; ?></td>
            <td data-label="Tytuł" class="prod-title"><?php echo esc_html($prod->post_title); ?></td>
            <td data-label="Cena"><?php echo $p_price!==''?esc_html(number_format_i18n((float)$p_price,2)).' zł':'-'; ?></td>
            <td data-label="Status">
              <?php
                $st = $prod->post_status;
                $badgeColor = ['pending'=>'#f59e0b','publish'=>'#059669','draft'=>'#64748b'];
                $label = ['pending'=>'Oczekuje','publish'=>'Opublikowany','draft'=>'Szkic'];
              ?>
              <span class="status-badge" style="--badge-color:<?php echo $badgeColor[$st]??'#94a3b8'; ?>;"><?php echo $label[$st]??$st; ?></span>
            </td>
            <td data-label="Data"><?php echo esc_html( get_the_date('Y-m-d',$prod) ); ?></td>
            <td data-label="Akcje">
              <div class="row-actions">
                <a href="<?php echo esc_url( add_query_arg('edit',$prod->ID) ); ?>#edit-product" class="act act-edit">Edytuj</a>
                <?php if($prod->post_status==='publish'): ?>
                  <a href="<?php echo get_permalink($prod->ID); ?>" target="_blank" class="act act-preview">Podgląd</a>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Na pewno przenieść do kosza?');" class="act-inline">
                  <?php wp_nonce_field('preomar_delete_product','preomar_delete_product_nonce'); ?>
                  <input type="hidden" name="delete_product_id" value="<?php echo esc_attr($prod->ID); ?>">
                  <button type="submit" class="act act-delete">Usuń</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php get_footer();
