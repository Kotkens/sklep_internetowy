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
  if( !current_user_can('publish_products') && !current_user_can('publish_posts') ) {
    $messages[]=['type'=>'error','text'=>'Brak uprawnień do publikacji produktów.'];
  } else {
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
        if($cats) wp_set_object_terms($post_id,$cats,'product_cat');
        preomar_upload_featured_image('product_image',$post_id,$messages,true);
        $messages[]=['type'=>'success','text'=>'Produkt zapisany i czeka na zatwierdzenie (#'.$post_id.').'];
      } else {
        $messages[]=['type'=>'error','text'=>'Błąd podczas tworzenia produktu'];
      }
    } else { foreach($errors as $e) $messages[]=['type'=>'error','text'=>$e]; }
  }}
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
if(!is_user_logged_in()) { echo '<div style="max-width:900px;margin:70px auto;padding:60px 50px;background:#fff;border:1px solid #e4e8ee;border-radius:20px;text-align:center;">';
 echo '<h1 style="margin:0 0 18px;font-size:2rem;font-weight:800;color:#1d2e49;">Musisz być zalogowany</h1>';
 echo '<p style="color:#5b6b80;font-size:1rem;margin:0 0 28px;">Zaloguj się aby wystawić przedmiot.</p>';
 echo '<a href="'.esc_url( wp_login_url( get_permalink() ) ).'" style="display:inline-block;background:#0d3b66;color:#fff;padding:14px 28px;border-radius:14px;font-weight:700;text-decoration:none;">Zaloguj się</a>';
 echo '</div>'; get_footer(); return; }
?>
<div class="sell-wrapper" style="max-width:1100px;margin:40px auto 80px;padding:0 20px;">
  <h1 style="font-size:2.2rem;margin:0 0 34px;font-weight:800;color:#1d2e49;">Wystaw przedmiot</h1>
  <?php if($messages): ?>
  <div style="display:flex;flex-direction:column;gap:10px;margin:0 0 28px;">
    <?php foreach($messages as $m): $ok=$m['type']==='success'; ?>
      <div style="padding:14px 18px;border:1px solid <?php echo $ok?'#b1f1d2':'#ffc9c4'; ?>;background:<?php echo $ok?'#eefdf5':'#fff4f3'; ?>;color:<?php echo $ok?'#065f46':'#9f1d12'; ?>;border-radius:12px;font-weight:600;font-size:.85rem;"><?php echo esc_html($m['text']); ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" style="background:#ffffff;border:1px solid #e5ebf2;border-radius:22px;padding:40px 46px;box-shadow:0 10px 28px -8px rgba(15,23,42,.12);margin-bottom:55px;">
    <?php wp_nonce_field('preomar_new_product','preomar_new_product_nonce'); ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:26px;">
      <div style="grid-column:1 / -1;">
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Tytuł *</label>
        <input type="text" name="product_title" required style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Cena (PLN) *</label>
  <input type="number" min="0" max="10000" step="0.01" name="product_price" required style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Cena promocyjna</label>
  <input type="number" min="0" max="10000" step="0.01" name="product_sale_price" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#fff9f4;font-weight:600;font-size:.95rem;">
        <div style="margin-top:6px;font-size:.55rem;color:#64748b;">(opcjonalnie – niższa niż regularna)</div>
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Promocja od</label>
        <input type="date" name="product_sale_from" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#fff9f4;font-weight:600;font-size:.8rem;">
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Promocja do</label>
        <input type="date" name="product_sale_to" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#fff9f4;font-weight:600;font-size:.8rem;">
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Ilość sztuk *</label>
        <input type="number" min="1" step="1" name="product_qty" value="1" required style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
        <div style="margin-top:6px;font-size:.55rem;color:#64748b;">Kupujący nie będzie mógł dodać do koszyka więcej niż dostępna ilość.</div>
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Kategoria *</label>
        <select name="product_cat[]" required style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
          <option value="">-- wybierz --</option>
          <?php foreach($categories as $cat): ?>
            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Zdjęcie główne *</label>
        <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" required style="width:100%;padding:11px 12px;border:1px dashed #c7d2e0;border-radius:12px;background:#f1f5f9;font-weight:500;font-size:.8rem;">
        <div style="margin-top:6px;font-size:.6rem;line-height:1.4;color:#64748b;font-weight:500;">
          Wymagania: JPG / PNG / WebP, min. 500x500 px, maks. 10 MB, brak ekstremalnych panoram (proporcje &lt; 3:1). Zalecane jasne tło i produkt wypełniający kadr.
        </div>
      </div>
      <div style="grid-column:1 / -1;">
        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Opis</label>
        <textarea name="product_desc" rows="6" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:500;font-size:.95rem;line-height:1.5;"></textarea>
      </div>
    </div>
    <div style="margin:38px 0 0;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
      <button type="submit" style="background:linear-gradient(135deg,#ff6b00,#ff832b);color:#fff;font-weight:700;padding:16px 34px;border:none;border-radius:16px;font-size:.95rem;letter-spacing:.5px;cursor:pointer;box-shadow:0 8px 24px -6px rgba(255,107,0,.4);transition:.35s;">Zapisz produkt</button>
      <a href="<?php echo esc_url( home_url('/ustawienia-konta/') ); ?>" style="font-size:.8rem;font-weight:600;color:#0d3b66;text-decoration:none;">Powrót do ustawień konta</a>
    </div>
  </form>
  <p style="margin:28px 0 0;font-size:.75rem;color:#64748b;">Po zapisaniu produkt trafi do moderacji. Po zatwierdzeniu pojawi się w katalogu.</p>

  <?php if($edit_product): ?>
    <?php 
      $edit_price = get_post_meta($edit_product->ID,'_regular_price',true); 
      $edit_terms = wp_get_post_terms($edit_product->ID,'product_cat',['fields'=>'ids']);
      $thumb_id = get_post_thumbnail_id($edit_product->ID);
      $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id,'thumbnail') : '';
    ?>
    <div id="edit-product" style="margin:70px 0 0;">
      <h2 style="font-size:1.6rem;margin:0 0 24px;font-weight:800;color:#1d2e49;">Edytuj produkt: <?php echo esc_html($edit_product->post_title); ?></h2>
      <form method="post" enctype="multipart/form-data" style="background:#ffffff;border:1px solid #e5ebf2;border-radius:22px;padding:34px 40px;box-shadow:0 10px 28px -8px rgba(15,23,42,.12);">
        <?php wp_nonce_field('preomar_edit_product','preomar_edit_product_nonce'); ?>
        <input type="hidden" name="edit_product_id" value="<?php echo esc_attr($edit_product->ID); ?>">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;">
          <div style="grid-column:1 / -1;">
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Tytuł *</label>
            <input type="text" name="edit_product_title" required value="<?php echo esc_attr($edit_product->post_title); ?>" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
          </div>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Cena (PLN) *</label>
            <input type="number" min="0" max="10000" step="0.01" name="edit_product_price" required value="<?php echo esc_attr($edit_price); ?>" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
          </div>
          <?php 
            $edit_sale_price = get_post_meta($edit_product->ID,'_sale_price',true);
            $edit_sale_from  = get_post_meta($edit_product->ID,'_sale_price_dates_from',true);
            $edit_sale_to    = get_post_meta($edit_product->ID,'_sale_price_dates_to',true);
            $edit_sale_from_fmt = $edit_sale_from ? date('Y-m-d', (int)$edit_sale_from) : '';
            $edit_sale_to_fmt   = $edit_sale_to   ? date('Y-m-d', (int)$edit_sale_to) : '';
          ?>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Cena promocyjna</label>
            <input type="number" min="0" max="10000" step="0.01" name="edit_product_sale_price" value="<?php echo esc_attr($edit_sale_price); ?>" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#fff9f4;font-weight:600;font-size:.95rem;">
          </div>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Promocja od</label>
            <input type="date" name="edit_product_sale_from" value="<?php echo esc_attr($edit_sale_from_fmt); ?>" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#fff9f4;font-weight:600;font-size:.8rem;">
          </div>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Promocja do</label>
            <input type="date" name="edit_product_sale_to" value="<?php echo esc_attr($edit_sale_to_fmt); ?>" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#fff9f4;font-weight:600;font-size:.8rem;">
          </div>
          <?php $edit_stock = (int)get_post_meta($edit_product->ID,'_stock',true); if($edit_stock<1) $edit_stock=1; ?>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Ilość sztuk *</label>
            <input type="number" min="1" step="1" name="edit_product_qty" required value="<?php echo esc_attr($edit_stock); ?>" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
          </div>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Kategoria *</label>
            <select name="edit_product_cat[]" required style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:600;font-size:.95rem;">
              <option value="">-- wybierz --</option>
              <?php foreach($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected( in_array($cat->term_id,$edit_terms,true) ); ?>><?php echo esc_html($cat->name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Zdjęcie (podmień)</label>
            <input type="file" name="edit_product_image" accept="image/jpeg,image/png,image/webp" style="width:100%;padding:11px 12px;border:1px dashed #c7d2e0;border-radius:12px;background:#f1f5f9;font-weight:500;font-size:.8rem;">
            <div style="margin-top:6px;font-size:.55rem;line-height:1.3;color:#64748b;font-weight:500;">
              Wymagania jak wyżej: min. 500x500 px, do 10 MB, format JPG/PNG/WebP.
            </div>
            <?php if($thumb_url): ?>
              <div style="margin-top:8px;font-size:.65rem;color:#64748b;display:flex;align-items:center;gap:10px;">
                <img src="<?php echo esc_url($thumb_url); ?>" style="width:46px;height:46px;object-fit:cover;border-radius:8px;border:1px solid #d4dce4;" alt="miniatura">
                <span>Aktualna miniatura</span>
              </div>
            <?php endif; ?>
          </div>
          <div style="grid-column:1 / -1;">
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#475569;margin:0 0 6px;">Opis</label>
            <textarea name="edit_product_desc" rows="6" style="width:100%;padding:14px 16px;border:1px solid #d4dce4;border-radius:12px;background:#f8fafc;font-weight:500;font-size:.95rem;line-height:1.5;"><?php echo esc_textarea($edit_product->post_content); ?></textarea>
          </div>
        </div>
        <div style="margin:34px 0 0;display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
          <button type="submit" style="background:#0d3b66;color:#fff;font-weight:700;padding:14px 30px;border:none;border-radius:14px;font-size:.9rem;cursor:pointer;">Zapisz zmiany</button>
          <a href="<?php echo esc_url( remove_query_arg('edit') ); ?>" style="font-size:.75rem;font-weight:600;color:#334155;text-decoration:none;">Anuluj edycję</a>
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
  <div style="margin:80px 0 0;">
    <h2 style="font-size:1.6rem;margin:0 0 24px;font-weight:800;color:#1d2e49;">Twoje produkty</h2>
    <div style="overflow:auto;border:1px solid #e2e8f0;border-radius:18px;background:#fff;">
      <table style="width:100%;border-collapse:collapse;font-size:.85rem;min-width:760px;">
        <thead>
          <tr style="background:#f1f5f9;text-align:left;">
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">ID</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">Miniatura</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">Tytuł</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">Cena</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">Status</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">Data</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;font-size:.65rem;letter-spacing:.5px;text-transform:uppercase;">Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($user_products as $prod):
              $p_price = get_post_meta($prod->ID,'_regular_price',true);
              $thumb = get_the_post_thumbnail($prod->ID,'thumbnail',['style'=>'width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;']);
          ?>
          <tr style="border-top:1px solid #f1f5f9;">
            <td style="padding:10px 16px;color:#334155;font-weight:600;">#<?php echo esc_html($prod->ID); ?></td>
            <td style="padding:10px 16px;"><?php echo $thumb ?: '<span style=\'font-size:.7rem;color:#94a3b8;\'>brak</span>'; ?></td>
            <td style="padding:10px 16px;color:#0f172a;font-weight:600;max-width:240px;"><?php echo esc_html($prod->post_title); ?></td>
            <td style="padding:10px 16px;color:#0f172a;"><?php echo $p_price!==''?esc_html(number_format((float)$p_price,2,',',' ')).' zł':'-'; ?></td>
            <td style="padding:10px 16px;">
              <?php
                $st = $prod->post_status;
                $badgeColor = ['pending'=>'#f59e0b','publish'=>'#059669','draft'=>'#64748b'];
                $label = ['pending'=>'Oczekuje','publish'=>'Opublikowany','draft'=>'Szkic'];
              ?>
              <span style="display:inline-block;background:<?php echo $badgeColor[$st]??'#94a3b8'; ?>;color:#fff;padding:4px 10px;border-radius:30px;font-size:.6rem;font-weight:700;letter-spacing:.5px;"><?php echo $label[$st]??$st; ?></span>
            </td>
            <td style="padding:10px 16px;color:#475569;"><?php echo esc_html( get_the_date('Y-m-d',$prod) ); ?></td>
            <td style="padding:10px 16px;">
              <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <a href="<?php echo esc_url( add_query_arg('edit',$prod->ID) ); ?>#edit-product" style="display:inline-block;background:#0d3b66;color:#fff;padding:6px 14px;border-radius:10px;font-size:.65rem;font-weight:700;text-decoration:none;">Edytuj</a>
                <?php if($prod->post_status==='publish'): ?>
                  <a href="<?php echo get_permalink($prod->ID); ?>" target="_blank" style="display:inline-block;background:#334155;color:#fff;padding:6px 12px;border-radius:10px;font-size:.65rem;font-weight:700;text-decoration:none;">Podgląd</a>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Na pewno przenieść do kosza?');" style="margin:0;">
                  <?php wp_nonce_field('preomar_delete_product','preomar_delete_product_nonce'); ?>
                  <input type="hidden" name="delete_product_id" value="<?php echo esc_attr($prod->ID); ?>">
                  <button type="submit" style="background:#b91c1c;color:#fff;border:none;padding:6px 12px;border-radius:10px;font-size:.65rem;font-weight:700;cursor:pointer;">Usuń</button>
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
